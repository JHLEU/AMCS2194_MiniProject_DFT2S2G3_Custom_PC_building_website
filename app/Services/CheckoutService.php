<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\SerialNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * Create an order from customer data and cart/build items within a DB transaction.
     *
     * @param  array<string, mixed>  $customerData  [customer_id, customer_name, customer_phone, customer_email, shipping_address]
     * @param  array<int, array{product_id: int, quantity?: int, unit_price?: float}>  $cartItems
     * @param  string  $paymentMethod
     * @param  bool    $autoPay
     * @return Order
     */
    public function createOrderFromCart(
        array $customerData,
        array $cartItems,
        string $paymentMethod = Payment::METHOD_DUMMY_SANDBOX,
        bool $autoPay = true
    ): Order {
        return DB::transaction(function () use ($customerData, $cartItems, $paymentMethod, $autoPay) {
            $orderNumber = $this->generateOrderNumber();

            $order = Order::create([
                'customer_id'          => $customerData['customer_id'] ?? null,
                'customer_name'        => trim($customerData['customer_name'] ?? 'Gamer'),
                'customer_phone'       => trim($customerData['customer_phone'] ?? ''),
                'customer_email'       => trim($customerData['customer_email'] ?? ''),
                'shipping_address'     => trim($customerData['shipping_address'] ?? 'Standard Delivery'),
                'order_number'         => $orderNumber,
                'total_selling_amount' => 0.00,
                'total_cost_amount'    => 0.00,
                'order_status'         => $autoPay ? Order::STATUS_PAID : Order::STATUS_PENDING,
                'tracking_number'      => $autoPay ? $this->generateTrackingNumber() : null,
                'carrier'              => $autoPay ? 'J&T Express Cargo' : null,
            ]);

            $totalSelling = 0.00;
            $estimatedTotalCost = 0.00;

            foreach ($cartItems as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                if ($productId <= 0) {
                    continue;
                }

                $product = Product::find($productId);
                if (! $product) {
                    continue;
                }

                $qty = max(1, (int) ($item['quantity'] ?? 1));
                $price = isset($item['unit_price']) && is_numeric($item['unit_price'])
                    ? (float) $item['unit_price']
                    : (float) $product->selling_price;

                OrderItem::create([
                    'order_id'   => $order->id,
                    'product_id' => $product->id,
                    'quantity'   => $qty,
                    'unit_price' => $price,
                ]);

                $totalSelling += ($price * $qty);
                $estimatedTotalCost += ((float) $product->cost_price * $qty);
            }

            $order->update([
                'total_selling_amount' => $totalSelling,
                'total_cost_amount'    => $estimatedTotalCost,
            ]);

            // Create Payment record
            $txnId = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(6));
            Payment::create([
                'order_id'       => $order->id,
                'payment_method' => $paymentMethod,
                'transaction_id' => $txnId,
                'amount'         => $totalSelling,
                'payment_status' => $autoPay ? Payment::STATUS_SUCCESS : Payment::STATUS_PENDING,
                'payment_date'   => $autoPay ? now() : null,
            ]);

            // If paid, allocate/reserve physical serial numbers & recalculate actual batch costs
            if ($autoPay) {
                $this->reserveSerialNumbers($order);
            }

            return $order->fresh(['items.product', 'payment', 'serialNumbers']);
        });
    }

    /**
     * Process dummy payment for an existing pending order.
     */
    public function processDummyPayment(
        Order $order,
        string $paymentMethod = Payment::METHOD_DUMMY_CARD,
        bool $simulateFailure = false
    ): Payment {
        return DB::transaction(function () use ($order, $paymentMethod, $simulateFailure) {
            $txnId = 'TXN-' . date('Ymd') . '-' . strtoupper(Str::random(6));

            $payment = $order->payment ?? new Payment(['order_id' => $order->id]);
            $payment->payment_method = $paymentMethod;
            $payment->transaction_id = $txnId;
            $payment->amount = $order->total_selling_amount;

            if ($simulateFailure) {
                $payment->payment_status = Payment::STATUS_FAILED;
                $payment->payment_date = null;
                $payment->save();

                return $payment;
            }

            $payment->payment_status = Payment::STATUS_SUCCESS;
            $payment->payment_date = now();
            $payment->save();

            $order->update([
                'order_status'    => Order::STATUS_PAID,
                'tracking_number' => $order->tracking_number ?? $this->generateTrackingNumber(),
                'carrier'         => $order->carrier ?? 'J&T Express Cargo',
            ]);

            $this->reserveSerialNumbers($order);

            return $payment;
        });
    }

    /**
     * Update order status with proper lifecycle transitions.
     */
    public function updateOrderStatus(
        Order $order,
        string $newStatus,
        ?string $trackingNumber = null,
        ?string $carrier = null
    ): Order {
        return DB::transaction(function () use ($order, $newStatus, $trackingNumber, $carrier) {
            $updates = ['order_status' => $newStatus];

            if ($trackingNumber !== null) {
                $updates['tracking_number'] = trim($trackingNumber);
            }

            if ($carrier !== null) {
                $updates['carrier'] = trim($carrier);
            }

            // Status-specific lifecycle actions
            if ($newStatus === Order::STATUS_COMPLETED) {
                $this->markSerialNumbersSold($order);
            } elseif ($newStatus === Order::STATUS_CANCELLED) {
                $this->releaseSerialNumbers($order);
            } elseif ($newStatus === Order::STATUS_PAID && $order->order_status !== Order::STATUS_PAID) {
                $this->reserveSerialNumbers($order);
            }

            $order->update($updates);

            return $order->fresh(['items.product', 'payment', 'serialNumbers']);
        });
    }

    /**
     * Reserve In-Stock physical serial numbers for line items in the order.
     */
    public function reserveSerialNumbers(Order $order): void
    {
        $actualHardwareCost = 0.00;

        foreach ($order->items as $item) {
            $neededQty = (int) $item->quantity;

            // Find In-Stock serial numbers for this product
            $availableSerials = SerialNumber::where('product_id', $item->product_id)
                ->where('status', SerialNumber::STATUS_IN_STOCK)
                ->limit($neededQty)
                ->get();

            foreach ($availableSerials as $sn) {
                $sn->update([
                    'status'   => SerialNumber::STATUS_RESERVED,
                    'order_id' => $order->id,
                ]);
                $actualHardwareCost += (float) $sn->batch_cost;
            }

            // If not enough serial numbers in stock, use product cost price for deficit
            $deficit = $neededQty - $availableSerials->count();
            if ($deficit > 0 && $item->product) {
                $actualHardwareCost += ($deficit * (float) $item->product->cost_price);
            }
        }

        if ($actualHardwareCost > 0) {
            $order->update(['total_cost_amount' => $actualHardwareCost]);
        }
    }

    /**
     * Transition all linked Reserved serial numbers to Sold upon order completion.
     */
    public function markSerialNumbersSold(Order $order): void
    {
        SerialNumber::where('order_id', $order->id)
            ->where('status', SerialNumber::STATUS_RESERVED)
            ->update(['status' => SerialNumber::STATUS_SOLD]);
    }

    /**
     * Release all linked serial numbers back to In-Stock if the order is cancelled.
     */
    public function releaseSerialNumbers(Order $order): void
    {
        SerialNumber::where('order_id', $order->id)
            ->update([
                'status'   => SerialNumber::STATUS_IN_STOCK,
                'order_id' => null,
            ]);
    }

    public function generateOrderNumber(): string
    {
        return 'ORD-' . date('Ymd') . '-' . strtoupper(Str::random(5));
    }

    public function generateTrackingNumber(): string
    {
        return 'JT-MY' . date('ymd') . rand(1000, 9999) . 'MY';
    }
}

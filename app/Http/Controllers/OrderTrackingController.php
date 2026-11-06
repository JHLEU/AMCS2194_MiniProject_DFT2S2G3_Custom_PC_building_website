<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OrderTrackingController extends Controller
{
    /**
     * Public order tracking page by order number or customer phone.
     */
    public function track(Request $request): View
    {
        $query = trim((string) ($request->input('query') ?? $request->input('order_number') ?? ''));
        $trackedOrder = null;
        $searched = false;

        if ($query !== '') {
            $searched = true;
            $trackedOrder = Order::where('order_number', $query)
                ->with(['items.product', 'payment', 'serialNumbers'])
                ->first();

            if (! $trackedOrder) {
                // Try phone match only if digits exist in query
                $cleanPhone = preg_replace('/[^\d]/', '', $query);
                if ($cleanPhone !== '') {
                    $trackedOrder = Order::where('customer_phone', 'like', '%' . $query . '%')
                        ->orWhereRaw("REPLACE(REPLACE(REPLACE(customer_phone, ' ', ''), '-', ''), '+', '') LIKE ?", ['%' . $cleanPhone . '%'])
                        ->latest()
                        ->with(['items.product', 'payment', 'serialNumbers'])
                        ->first();
                }
            }
        }

        // Ordered timeline steps
        $milestoneSteps = [
            Order::STATUS_PENDING    => ['title' => 'Pending Payment', 'desc' => 'Order created and awaiting settlement.', 'icon' => '📝'],
            Order::STATUS_PAID       => ['title' => 'Payment Confirmed', 'desc' => 'Transaction verified. Hardware parts allocated.', 'icon' => '💳'],
            Order::STATUS_ASSEMBLING => ['title' => 'Bench Assembly', 'desc' => 'Technician currently assembling chassis and cabling.', 'icon' => '🔧'],
            Order::STATUS_TESTING    => ['title' => 'Testing & QC', 'desc' => 'BIOS tuning, thermal stress testing & benchmark run.', 'icon' => '⚡'],
            Order::STATUS_SHIPPED    => ['title' => 'Dispatched', 'desc' => 'Packaged in shockproof carton and handed to courier.', 'icon' => '🚚'],
            Order::STATUS_COMPLETED  => ['title' => 'Delivered', 'desc' => 'Handed over to customer / signed upon delivery.', 'icon' => '✔'],
        ];

        return view('orders.track', [
            'query'          => $query,
            'searched'       => $searched,
            'trackedOrder'   => $trackedOrder,
            'milestoneSteps' => $milestoneSteps,
        ]);
    }

    /**
     * Authenticated customer order history.
     */
    public function myOrders(Request $request): View
    {
        $user = Auth::user();

        $orders = Order::where(function ($q) use ($user) {
            $q->where('customer_id', $user->id)
              ->orWhere('customer_email', $user->email);
        })
        ->with(['items.product', 'payment'])
        ->latest()
        ->paginate(10);

        return view('orders.index', [
            'orders' => $orders,
        ]);
    }
}

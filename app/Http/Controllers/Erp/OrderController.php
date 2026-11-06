<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * Orders Queue & Operations overview.
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $search = $request->query('search');

        $orders = Order::filter($status, $search)
            ->with(['items.product', 'payment'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $metrics = [
            'total'      => Order::count(),
            'paid'       => Order::where('order_status', Order::STATUS_PAID)->count(),
            'assembling' => Order::where('order_status', Order::STATUS_ASSEMBLING)->count(),
            'testing'    => Order::where('order_status', Order::STATUS_TESTING)->count(),
            'shipped'    => Order::where('order_status', Order::STATUS_SHIPPED)->count(),
            'completed'  => Order::where('order_status', Order::STATUS_COMPLETED)->count(),
            'revenue'    => (float) Order::where('order_status', '!=', Order::STATUS_CANCELLED)->sum('total_selling_amount'),
        ];

        return view('erp.orders.index', [
            'orders'  => $orders,
            'metrics' => $metrics,
            'status'  => $status,
            'search'  => $search,
        ]);
    }

    /**
     * Order detail view with assigned serial numbers & lifecycle actions.
     */
    public function show(Order $order): View
    {
        $order->load(['items.product', 'payment', 'serialNumbers.product', 'customer']);

        return view('erp.orders.show', [
            'order' => $order,
        ]);
    }

    /**
     * Update order workflow status.
     */
    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'order_status'    => ['required', 'string', 'in:' . implode(',', Order::STATUSES)],
            'tracking_number' => ['nullable', 'string', 'max:100'],
            'carrier'         => ['nullable', 'string', 'max:50'],
        ]);

        $this->checkoutService->updateOrderStatus(
            $order,
            $validated['order_status'],
            $validated['tracking_number'] ?? null,
            $validated['carrier'] ?? null
        );

        return redirect()->route('erp.orders.show', $order)
            ->with('success', "Order status successfully updated to {$validated['order_status']}!");
    }

    /**
     * Update order shipping tracking number & carrier.
     */
    public function updateShipping(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'tracking_number' => ['required', 'string', 'max:100'],
            'carrier'         => ['required', 'string', 'max:50'],
        ]);

        $order->update([
            'tracking_number' => trim($validated['tracking_number']),
            'carrier'         => trim($validated['carrier']),
        ]);

        return redirect()->route('erp.orders.show', $order)
            ->with('success', 'Shipping carrier and tracking consignment updated!');
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Services\CheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CheckoutService $checkoutService
    ) {}

    /**
     * Display the checkout review & payment gateway screen.
     */
    public function index(Request $request): View
    {
        $productIds = [];

        if ($request->filled('product_ids')) {
            $raw = $request->input('product_ids');
            $productIds = is_array($raw) ? $raw : explode(',', (string) $raw);
        } elseif ($request->filled('products')) {
            $productIds = (array) $request->input('products');
        }

        $productIds = array_filter(array_map('intval', $productIds));

        // Default flagship bundle if empty
        if (empty($productIds)) {
            $defaultProducts = Product::whereIn('id', [1, 3, 4, 5, 6, 7])->get();
            if ($defaultProducts->isEmpty()) {
                $defaultProducts = Product::limit(6)->get();
            }
        } else {
            $defaultProducts = Product::whereIn('id', $productIds)->get();
        }

        $items = $defaultProducts->map(function (Product $prod) {
            return [
                'product'    => $prod,
                'quantity'   => 1,
                'unit_price' => (float) $prod->selling_price,
                'subtotal'   => (float) $prod->selling_price,
            ];
        });

        $totalAmount = $items->sum('subtotal');

        $user = Auth::user();
        $defaultCustomer = [
            'name'    => $user?->name ?? 'Bryan Tan',
            'phone'   => $user?->phone ?? '+60 12-345 6789',
            'email'   => $user?->email ?? 'customer@custompc.local',
            'address' => '12A, Jalan Kiara 3, Mont Kiara, 50480 Kuala Lumpur',
        ];

        return view('checkout.index', [
            'items'           => $items,
            'totalAmount'     => $totalAmount,
            'defaultCustomer' => $defaultCustomer,
            'paymentMethods'  => [
                [
                    'id'          => Payment::METHOD_DUMMY_SANDBOX,
                    'name'        => 'Instant Sandbox (1-Click Test)',
                    'badge'       => 'Fastest Demo',
                    'badge_class' => 'badge-success',
                    'icon'        => '⚡',
                    'desc'        => 'Simulate instant verified transaction with zero credentials required.',
                    'enabled'     => true,
                ],
                [
                    'id'          => Payment::METHOD_DUMMY_CARD,
                    'name'        => 'Mock Credit / Debit Card',
                    'badge'       => 'Visa / Mastercard',
                    'badge_class' => 'badge-info',
                    'icon'        => '💳',
                    'desc'        => 'Simulated card gateway with test 4242 4242 numbers.',
                    'enabled'     => true,
                ],
                [
                    'id'          => Payment::METHOD_DUMMY_FPX,
                    'name'        => 'Mock FPX Online Banking',
                    'badge'       => 'Maybank2u / CIMB',
                    'badge_class' => 'badge-primary',
                    'icon'        => '🏦',
                    'desc'        => 'Simulated Malaysian online direct banking transfer.',
                    'enabled'     => true,
                ],
                [
                    'id'          => 'stripe_live',
                    'name'        => 'Stripe Live API Gateway',
                    'badge'       => 'Coming Soon',
                    'badge_class' => 'badge-secondary',
                    'icon'        => '🔒',
                    'desc'        => 'Production Stripe Elements payment intent integration (Scheduled after base rollout).',
                    'enabled'     => false,
                ],
            ],
        ]);
    }

    /**
     * Process checkout form submission.
     */
    public function process(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'customer_name'    => ['required', 'string', 'max:100'],
            'customer_phone'   => ['required', 'string', 'max:50'],
            'customer_email'   => ['required', 'email', 'max:100'],
            'shipping_address' => ['required', 'string', 'max:500'],
            'payment_method'   => ['required', 'string'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
            'items.*.quantity'   => ['nullable', 'integer', 'min:1'],
        ]);

        $customerData = [
            'customer_id'      => Auth::id(),
            'customer_name'    => $validated['customer_name'],
            'customer_phone'   => $validated['customer_phone'],
            'customer_email'   => $validated['customer_email'],
            'shipping_address' => $validated['shipping_address'],
        ];

        // Ensure supported dummy payment method (fallback if Stripe was selected)
        $method = $validated['payment_method'];
        if ($method === 'stripe_live' || ! in_array($method, Payment::METHODS, true)) {
            $method = Payment::METHOD_DUMMY_SANDBOX;
        }

        $order = $this->checkoutService->createOrderFromCart(
            $customerData,
            $validated['items'],
            $method,
            true // Auto-confirm dummy payment
        );

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success'        => true,
                'order_number'   => $order->order_number,
                'transaction_id' => $order->payment?->transaction_id,
                'tracking_url'   => route('orders.track', ['query' => $order->order_number]),
                'receipt_url'    => route('checkout.success', $order->order_number),
                'message'        => 'Payment confirmed! Order has been received and routed to technical assembly.',
            ]);
        }

        return redirect()->route('checkout.success', $order->order_number)
            ->with('success', 'Order created & payment verified successfully!');
    }

    /**
     * Display order success receipt page.
     */
    public function success(string $orderNumber): View
    {
        $order = Order::where('order_number', $orderNumber)
            ->with(['items.product', 'payment', 'serialNumbers'])
            ->firstOrFail();

        return view('checkout.success', [
            'order' => $order,
        ]);
    }
}

<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\RestockOrder;
use App\Models\RestockOrderItem;
use App\Models\SerialNumber;
use App\Models\Supplier;
use App\Models\SupplierHardwareMapping;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RestockController extends Controller
{
    public function index(Request $request): View
    {
        $selectedStatus = trim((string) $request->query('status', ''));
        $selectedSupplier = (int) $request->query('supplier_id', 0);

        $query = RestockOrder::with('supplier')
            ->withCount('items as total_line_items')
            ->withSum('items as total_units_ordered', 'quantity');

        if ($selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        if ($selectedSupplier > 0) {
            $query->where('supplier_id', $selectedSupplier);
        }

        $orders = $query->orderByDesc('created_at')->orderByDesc('id')->get();
        $suppliers = Supplier::orderBy('company_name')->get();

        return view('erp.restock.index', compact(
            'orders',
            'suppliers',
            'selectedStatus',
            'selectedSupplier'
        ));
    }

    public function create(Request $request): View
    {
        $suppliers = Supplier::withCount('hardwareMappings as mapped_products_count')
            ->orderBy('company_name')
            ->get();
        $allProducts = Product::orderBy('category')->orderBy('name')->get();
        $preselectedSupplier = (int) $request->query('supplier_id', 0);
        $preselectedProduct = (int) $request->query('product_id', 0);

        return view('erp.restock.create', compact(
            'suppliers',
            'allProducts',
            'preselectedSupplier',
            'preselectedProduct'
        ));
    }

    public function supplierProducts(Request $request): JsonResponse
    {
        $supplierId = (int) $request->query('supplier_id', 0);
        if ($supplierId <= 0) {
            return response()->json(['success' => false, 'products' => []]);
        }

        $mapped = SupplierHardwareMapping::with('product')
            ->where('supplier_id', $supplierId)
            ->get()
            ->map(fn (SupplierHardwareMapping $m) => [
                'product_id'        => $m->product_id,
                'product_name'      => $m->product->name ?? '',
                'product_category'  => $m->product->category ?? '',
                'supplier_sku'      => $m->supplier_sku,
                'agreed_unit_price' => (float) $m->agreed_unit_price,
                'lead_time_days'    => (int) $m->lead_time_days,
            ])
            ->values();

        return response()->json([
            'success'  => true,
            'products' => $mapped,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'supplier_id'          => ['required', 'exists:suppliers,id'],
            'order_date'           => ['required', 'date'],
            'expected_date'        => ['nullable', 'date'],
            'notes'                => ['nullable', 'string'],
            'items'                => ['required', 'array', 'min:1'],
            'items.*.product_id'   => ['required', 'exists:products,id'],
            'items.*.quantity'     => ['required', 'integer', 'min:1'],
            'items.*.unit_cost'    => ['required', 'numeric', 'min:0'],
        ]);

        $order = DB::transaction(function () use ($validated) {
            $totalAmount = 0.0;
            foreach ($validated['items'] as $item) {
                $totalAmount += ((int) $item['quantity']) * ((float) $item['unit_cost']);
            }

            $restockOrder = RestockOrder::create([
                'order_code'    => RestockOrder::generateOrderCode(),
                'supplier_id'   => (int) $validated['supplier_id'],
                'status'        => RestockOrder::STATUS_PENDING,
                'total_amount'  => $totalAmount,
                'order_date'    => $validated['order_date'],
                'expected_date' => $validated['expected_date'] ?? null,
                'notes'         => $validated['notes'] ?? null,
            ]);

            foreach ($validated['items'] as $item) {
                $qty = (int) $item['quantity'];
                $cost = (float) $item['unit_cost'];

                RestockOrderItem::create([
                    'restock_order_id' => $restockOrder->id,
                    'product_id'       => (int) $item['product_id'],
                    'quantity'         => $qty,
                    'unit_cost'        => $cost,
                    'subtotal'         => $qty * $cost,
                ]);
            }

            return $restockOrder;
        });

        return redirect()
            ->route('erp.restock.show', $order)
            ->with('success', "Restock order {$order->order_code} created successfully.");
    }

    public function show(RestockOrder $restockOrder): View
    {
        $restockOrder->load(['supplier', 'items.product', 'serialNumbers.product']);

        $skuMap = SupplierHardwareMapping::where('supplier_id', $restockOrder->supplier_id)
            ->pluck('supplier_sku', 'product_id');

        return view('erp.restock.show', [
            'order'  => $restockOrder,
            'items'  => $restockOrder->items,
            'skuMap' => $skuMap,
        ]);
    }

    public function updateStatus(Request $request, RestockOrder $restockOrder): RedirectResponse
    {
        $validated = $request->validate([
            'status'           => ['required', Rule::in(RestockOrder::STATUSES)],
            'auto_generate_sn' => ['nullable', 'boolean'],
        ]);

        $newStatus = $validated['status'];
        $autoGenerateSn = $request->boolean('auto_generate_sn');

        // Idempotency guard: if already Completed, do not re-generate SNs
        if ($restockOrder->status === RestockOrder::STATUS_COMPLETED && $newStatus === RestockOrder::STATUS_COMPLETED) {
            return redirect()
                ->route('erp.restock.show', $restockOrder)
                ->with('success', "Order #{$restockOrder->order_code} is already marked Completed.");
        }

        $generatedTotal = 0;

        DB::transaction(function () use ($restockOrder, $newStatus, $autoGenerateSn, &$generatedTotal) {
            $updateData = ['status' => $newStatus];
            if ($newStatus === RestockOrder::STATUS_COMPLETED && !$restockOrder->completed_at) {
                $updateData['completed_at'] = now();
            }

            $restockOrder->update($updateData);

            if (
                $newStatus === RestockOrder::STATUS_COMPLETED
                && $autoGenerateSn
                && !$restockOrder->serialNumbers()->exists()
            ) {
                $restockOrder->load('items');

                foreach ($restockOrder->items as $item) {
                    $prodId = (int) $item->product_id;
                    $qty = (int) $item->quantity;
                    $cost = (float) $item->unit_cost;

                    $codes = [];
                    for ($i = 1; $i <= $qty; $i++) {
                        $uniqueSuffix = strtoupper(substr(bin2hex(random_bytes(3)), 0, 5));
                        $codes[] = "SN-P{$prodId}-" . date('ymd') . "-{$uniqueSuffix}";
                    }

                    $res = SerialNumber::bulkRegister($prodId, $codes, $cost, $restockOrder->id);
                    $generatedTotal += $res['inserted'];
                }
            }
        });

        $message = $generatedTotal > 0
            ? "Order #{$restockOrder->order_code} marked Completed. Automatically provisioned {$generatedTotal} 'In-Stock' serial number(s)."
            : "Order #{$restockOrder->order_code} status changed to '{$newStatus}'.";

        return redirect()
            ->route('erp.restock.show', $restockOrder)
            ->with('success', $message);
    }

    public function history(Request $request): View
    {
        $selectedSupplier = (int) $request->query('supplier_id', 0);
        $dateFrom = trim((string) $request->query('date_from', ''));
        $dateTo = trim((string) $request->query('date_to', ''));
        $search = trim((string) $request->query('search', ''));

        $query = RestockOrder::with('supplier')
            ->withCount('items as total_line_items')
            ->withSum('items as total_units_received', 'quantity')
            ->where('status', RestockOrder::STATUS_COMPLETED);

        if ($selectedSupplier > 0) {
            $query->where('supplier_id', $selectedSupplier);
        }

        if ($dateFrom !== '') {
            $query->whereDate('order_date', '>=', $dateFrom);
        }

        if ($dateTo !== '') {
            $query->whereDate('order_date', '<=', $dateTo);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('order_code', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($sq) => $sq->where('company_name', 'like', "%{$search}%"));
            });
        }

        $history = $query->orderByDesc('completed_at')->orderByDesc('order_date')->get();

        $completedBase = RestockOrder::where('status', RestockOrder::STATUS_COMPLETED);
        $totalCompletedOrders = (clone $completedBase)->count();
        $totalSpent = (float) ((clone $completedBase)->sum('total_amount') ?? 0.0);
        $totalItemsProcured = (int) RestockOrderItem::whereIn(
            'restock_order_id',
            (clone $completedBase)->select('id')
        )->sum('quantity');

        $metrics = [
            'total_completed_orders' => $totalCompletedOrders,
            'total_spent'            => $totalSpent,
            'total_items_procured'   => $totalItemsProcured,
        ];

        $suppliers = Supplier::orderBy('company_name')->get();

        return view('erp.restock.history', compact(
            'history',
            'metrics',
            'suppliers',
            'selectedSupplier',
            'dateFrom',
            'dateTo',
            'search'
        ));
    }
}

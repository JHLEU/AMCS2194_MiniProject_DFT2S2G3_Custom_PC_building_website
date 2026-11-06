<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InventoryController extends Controller
{
    public function index(): View
    {
        $costing = SerialNumber::getInventoryCostingSummary();
        $lowStockProducts = Product::lowStock()
            ->get()
            ->sortByDesc(fn (Product $p) => $p->deficit)
            ->values();
        $totalProducts = Product::count();
        $totalSuppliers = Supplier::count();

        return view('erp.inventory.dashboard', compact(
            'costing',
            'lowStockProducts',
            'totalProducts',
            'totalSuppliers'
        ));
    }

    public function lowStock(): View
    {
        $lowStockItems = Product::lowStock()
            ->get()
            ->sortByDesc(fn (Product $p) => $p->deficit)
            ->values();

        return view('erp.inventory.low_stock', compact('lowStockItems'));
    }

    public function costing(): View
    {
        $costingData = SerialNumber::getInventoryCostingSummary();

        return view('erp.inventory.costing', compact('costingData'));
    }

    public function serials(Request $request): View
    {
        $selectedStatus = trim((string) $request->query('status', ''));
        $selectedProduct = (int) $request->query('product_id', 0);
        $search = trim((string) $request->query('search', ''));

        $query = SerialNumber::with(['product', 'restockOrder']);

        if ($selectedStatus !== '') {
            $query->where('status', $selectedStatus);
        }

        if ($selectedProduct > 0) {
            $query->where('product_id', $selectedProduct);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('sn_code', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
            });
        }

        $serials = $query->orderByDesc('created_at')->orderByDesc('id')->get();
        $products = Product::orderBy('category')->orderBy('name')->get();
        $statuses = SerialNumber::STATUSES;

        return view('erp.serials.index', compact(
            'serials',
            'products',
            'statuses',
            'selectedStatus',
            'selectedProduct',
            'search'
        ));
    }

    public function createSerial(Request $request): View
    {
        $products = Product::withStockCounts()->orderBy('category')->orderBy('name')->get();
        $preselectedProduct = (int) $request->query('product_id', 0);
        $statuses = SerialNumber::STATUSES;

        return view('erp.serials.create', compact(
            'products',
            'preselectedProduct',
            'statuses'
        ));
    }

    public function storeSerial(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'    => ['required', 'exists:products,id'],
            'mode'          => ['required', 'in:single,bulk'],
            'batch_cost'    => ['nullable', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(SerialNumber::STATUSES)],
            'sn_code'       => ['nullable', 'string', 'max:100'],
            'bulk_sn_codes' => ['nullable', 'string'],
        ]);

        $product = Product::findOrFail((int) $validated['product_id']);
        $batchCost = isset($validated['batch_cost']) && $validated['batch_cost'] !== '' && (float) $validated['batch_cost'] > 0
            ? (float) $validated['batch_cost']
            : (float) $product->cost_price;
        $status = $validated['status'];

        if ($validated['mode'] === 'bulk') {
            $rawCodes = trim((string) ($validated['bulk_sn_codes'] ?? ''));
            $snList = preg_split('/[\r\n,]+/', $rawCodes, -1, PREG_SPLIT_NO_EMPTY) ?: [];

            if (empty($snList)) {
                return back()
                    ->withInput()
                    ->withErrors(['bulk_sn_codes' => 'Please provide at least one serial number code.']);
            }

            $result = SerialNumber::bulkRegister($product->id, $snList, $batchCost, null, $status);

            $msg = "Batch registered: {$result['inserted']} SN(s) added.";
            if ($result['skipped'] > 0) {
                $msg .= " ({$result['skipped']} duplicate(s) skipped).";
            }

            return redirect()->route('erp.serials.index')->with('success', $msg);
        }

        $snCode = trim((string) ($validated['sn_code'] ?? ''));
        if ($snCode === '') {
            return back()
                ->withInput()
                ->withErrors(['sn_code' => 'Serial number code cannot be empty.']);
        }

        if (SerialNumber::where('sn_code', $snCode)->exists()) {
            return back()
                ->withInput()
                ->withErrors(['sn_code' => "Serial number '{$snCode}' already exists in the system."]);
        }

        SerialNumber::create([
            'sn_code'    => $snCode,
            'product_id' => $product->id,
            'status'     => $status,
            'batch_cost' => $batchCost,
        ]);

        return redirect()
            ->route('erp.serials.index')
            ->with('success', "Serial number '{$snCode}' registered successfully.");
    }

    public function updateSerialStatus(Request $request, SerialNumber $serialNumber): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(SerialNumber::STATUSES)],
        ]);

        $serialNumber->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('erp.serials.index')
            ->with('success', "Serial number {$serialNumber->sn_code} status changed to '{$validated['status']}'.");
    }

    public function destroySerial(SerialNumber $serialNumber): RedirectResponse
    {
        $code = $serialNumber->sn_code;
        $serialNumber->delete();

        return redirect()
            ->route('erp.serials.index')
            ->with('success', "Serial number {$code} removed from tracking.");
    }
}

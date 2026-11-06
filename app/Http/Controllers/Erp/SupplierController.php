<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\SupplierHardwareMapping;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SupplierController extends Controller
{
    public function index(): View
    {
        $suppliers = Supplier::withCount([
            'hardwareMappings as mapped_products_count',
            'restockOrders as total_orders_count',
        ])->orderBy('company_name')->get();

        return view('erp.suppliers.index', compact('suppliers'));
    }

    public function create(): View
    {
        return view('erp.suppliers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:150'],
            'phone'          => ['required', 'string', 'max:50'],
            'email'          => ['required', 'email', 'max:150', 'unique:suppliers,email'],
            'address'        => ['nullable', 'string'],
        ]);

        $supplier = Supplier::create($validated);

        return redirect()
            ->route('erp.suppliers.mapping', $supplier)
            ->with('success', "Supplier '{$supplier->company_name}' registered successfully.");
    }

    public function edit(Supplier $supplier): View
    {
        return view('erp.suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'company_name'   => ['required', 'string', 'max:255'],
            'contact_person' => ['required', 'string', 'max:150'],
            'phone'          => ['required', 'string', 'max:50'],
            'email'          => ['required', 'email', 'max:150', Rule::unique('suppliers', 'email')->ignore($supplier->id)],
            'address'        => ['nullable', 'string'],
        ]);

        $supplier->update($validated);

        return redirect()
            ->route('erp.suppliers.index')
            ->with('success', 'Supplier profile updated successfully.');
    }

    public function destroy(Supplier $supplier): RedirectResponse
    {
        if ($supplier->restockOrders()->exists()) {
            return redirect()
                ->route('erp.suppliers.index')
                ->with('error', "Cannot remove supplier '{$supplier->company_name}' because restock orders exist for this vendor.");
        }

        try {
            $name = $supplier->company_name;
            $supplier->delete();

            return redirect()
                ->route('erp.suppliers.index')
                ->with('success', "Supplier '{$name}' removed.");
        } catch (QueryException) {
            return redirect()
                ->route('erp.suppliers.index')
                ->with('error', "Cannot remove supplier '{$supplier->company_name}' due to linked records.");
        }
    }

    public function mapping(Supplier $supplier): View
    {
        $mappedProducts = SupplierHardwareMapping::with('product')
            ->where('supplier_id', $supplier->id)
            ->get()
            ->sortBy(fn ($m) => ($m->product->category ?? '') . '_' . ($m->product->name ?? ''))
            ->values();

        $allProducts = Product::orderBy('category')->orderBy('name')->get();

        return view('erp.suppliers.mapping', compact(
            'supplier',
            'mappedProducts',
            'allProducts'
        ));
    }

    public function saveMapping(Request $request, Supplier $supplier): RedirectResponse
    {
        $validated = $request->validate([
            'product_id'        => ['required', 'exists:products,id'],
            'supplier_sku'      => ['nullable', 'string', 'max:100'],
            'agreed_unit_price' => ['required', 'numeric', 'min:0.01'],
            'lead_time_days'    => ['required', 'integer', 'min:1'],
        ]);

        SupplierHardwareMapping::updateOrCreate(
            [
                'supplier_id' => $supplier->id,
                'product_id'  => (int) $validated['product_id'],
            ],
            [
                'supplier_sku'      => $validated['supplier_sku'] ?: null,
                'agreed_unit_price' => (float) $validated['agreed_unit_price'],
                'lead_time_days'    => (int) $validated['lead_time_days'],
            ]
        );

        return redirect()
            ->route('erp.suppliers.mapping', $supplier)
            ->with('success', 'Hardware supply mapping saved successfully.');
    }

    public function removeMapping(Supplier $supplier, Product $product): RedirectResponse
    {
        SupplierHardwareMapping::where('supplier_id', $supplier->id)
            ->where('product_id', $product->id)
            ->delete();

        return redirect()
            ->route('erp.suppliers.mapping', $supplier)
            ->with('success', 'Product unmapped from this supplier.');
    }
}

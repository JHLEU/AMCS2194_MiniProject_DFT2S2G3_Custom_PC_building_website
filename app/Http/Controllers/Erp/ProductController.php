<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $selectedCategory = trim((string) $request->query('category', ''));
        $search = trim((string) $request->query('search', ''));

        $query = Product::withStockCounts()->with('defaultSupplier');

        if ($selectedCategory !== '') {
            $query->where('category', $selectedCategory);
        }

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('brand', 'like', "%{$search}%")
                    ->orWhere('specs', 'like', "%{$search}%")
                    ->orWhere('socket_type', 'like', "%{$search}%")
                    ->orWhere('ram_type', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('category')->orderBy('name')->get();
        $categories = Product::CATEGORIES;

        return view('erp.products.index', compact(
            'products',
            'categories',
            'selectedCategory',
            'search'
        ));
    }

    public function create(): View
    {
        $categories = Product::CATEGORIES;
        $socketTypes = Product::SOCKET_TYPES;
        $ramTypes = Product::RAM_TYPES;
        $suppliers = Supplier::orderBy('company_name')->get();

        return view('erp.products.create', compact(
            'categories',
            'socketTypes',
            'ramTypes',
            'suppliers'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'category'            => ['required', 'string', Rule::in(Product::CATEGORIES)],
            'supplier_id'         => ['nullable', 'exists:suppliers,id'],
            'brand'               => ['nullable', 'string', 'max:100'],
            'socket_type'         => ['nullable', 'string', 'max:50'],
            'ram_type'            => ['nullable', 'string', 'max:20'],
            'tdp_wattage'         => ['nullable', 'integer', 'min:0'],
            'cost_price'          => ['required', 'numeric', 'min:0'],
            'selling_price'       => ['required', 'numeric', 'min:0'],
            'warranty_months'     => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:1'],
            'specs'               => ['nullable', 'string'],
        ]);

        $categoryModel = Category::firstOrCreate(
            ['name' => $validated['category']],
            ['slug' => Str::slug($validated['category'])]
        );

        $product = Product::create([
            'name'                => $validated['name'],
            'category'            => $validated['category'],
            'category_id'         => $categoryModel->id,
            'supplier_id'         => $validated['supplier_id'] ?? null,
            'brand'               => $validated['brand'] ?? null,
            'socket_type'         => $validated['socket_type'] ?: null,
            'ram_type'            => $validated['ram_type'] ?: null,
            'tdp_wattage'         => (int) ($validated['tdp_wattage'] ?? 0),
            'cost_price'          => (float) $validated['cost_price'],
            'selling_price'       => (float) $validated['selling_price'],
            'warranty_months'     => (int) ($validated['warranty_months'] ?? 12),
            'low_stock_threshold' => (int) $validated['low_stock_threshold'],
            'specs'               => $validated['specs'] ?? null,
        ]);

        return redirect()
            ->route('erp.products.index')
            ->with('success', "Product '{$product->name}' created successfully (ID #{$product->id}).");
    }

    public function edit(Product $product): View
    {
        $product->loadCount([
            'serialNumbers as in_stock_count' => fn ($q) => $q->where('status', 'In-Stock'),
        ]);
        $product->load(['supplierMappings.supplier']);

        $categories = Product::CATEGORIES;
        $socketTypes = Product::SOCKET_TYPES;
        $ramTypes = Product::RAM_TYPES;
        $suppliers = Supplier::orderBy('company_name')->get();

        return view('erp.products.edit', compact(
            'product',
            'categories',
            'socketTypes',
            'ramTypes',
            'suppliers'
        ));
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name'                => ['required', 'string', 'max:255'],
            'category'            => ['required', 'string', Rule::in(Product::CATEGORIES)],
            'supplier_id'         => ['nullable', 'exists:suppliers,id'],
            'brand'               => ['nullable', 'string', 'max:100'],
            'socket_type'         => ['nullable', 'string', 'max:50'],
            'ram_type'            => ['nullable', 'string', 'max:20'],
            'tdp_wattage'         => ['nullable', 'integer', 'min:0'],
            'cost_price'          => ['required', 'numeric', 'min:0'],
            'selling_price'       => ['required', 'numeric', 'min:0'],
            'warranty_months'     => ['nullable', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:1'],
            'specs'               => ['nullable', 'string'],
        ]);

        $categoryModel = Category::firstOrCreate(
            ['name' => $validated['category']],
            ['slug' => Str::slug($validated['category'])]
        );

        $product->update([
            'name'                => $validated['name'],
            'category'            => $validated['category'],
            'category_id'         => $categoryModel->id,
            'supplier_id'         => $validated['supplier_id'] ?? null,
            'brand'               => $validated['brand'] ?? null,
            'socket_type'         => $validated['socket_type'] ?: null,
            'ram_type'            => $validated['ram_type'] ?: null,
            'tdp_wattage'         => (int) ($validated['tdp_wattage'] ?? 0),
            'cost_price'          => (float) $validated['cost_price'],
            'selling_price'       => (float) $validated['selling_price'],
            'warranty_months'     => (int) ($validated['warranty_months'] ?? 12),
            'low_stock_threshold' => (int) $validated['low_stock_threshold'],
            'specs'               => $validated['specs'] ?? null,
        ]);

        return redirect()
            ->route('erp.products.index')
            ->with('success', "Product '{$product->name}' updated successfully.");
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->restockOrderItems()->exists()) {
            return redirect()
                ->route('erp.products.index')
                ->with('error', "Cannot delete '{$product->name}' because it is referenced by existing restock purchase orders.");
        }

        try {
            $name = $product->name;
            $product->delete();

            return redirect()
                ->route('erp.products.index')
                ->with('success', "Product '{$name}' deleted successfully.");
        } catch (QueryException) {
            return redirect()
                ->route('erp.products.index')
                ->with('error', "Cannot delete '{$product->name}' due to linked order or inventory records.");
        }
    }
}

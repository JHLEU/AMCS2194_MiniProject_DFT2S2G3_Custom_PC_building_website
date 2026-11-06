<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\CompatibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalculatorController extends Controller
{
    public function __construct(
        protected CompatibilityService $compatibilityService
    ) {}

    public function index(): View
    {
        $cpus = Product::where('category', 'CPU')->orderBy('selling_price', 'desc')->get();
        $gpus = Product::where('category', 'GPU')->orderBy('selling_price', 'desc')->get();
        $rams = Product::where('category', 'RAM')->orderBy('selling_price', 'desc')->get();
        $storages = Product::where('category', 'Storage')->orderBy('selling_price', 'desc')->get();

        return view('calculator.index', compact('cpus', 'gpus', 'rams', 'storages'));
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cpu_id'     => 'required|exists:products,id',
            'gpu_id'     => 'required|exists:products,id',
            'ram_id'     => 'nullable|exists:products,id',
            'storage_id' => 'nullable|exists:products,id',
        ]);

        $cpu = Product::findOrFail((int) $validated['cpu_id']);
        $gpu = Product::findOrFail((int) $validated['gpu_id']);
        $ram = ! empty($validated['ram_id'])
            ? Product::findOrFail((int) $validated['ram_id'])
            : Product::where('category', 'RAM')->orderBy('selling_price', 'desc')->first();
        $storage = ! empty($validated['storage_id'])
            ? Product::findOrFail((int) $validated['storage_id'])
            : Product::where('category', 'Storage')->orderBy('selling_price', 'desc')->first();

        $result = $this->compatibilityService->calculatePerformance(
            $cpu,
            $gpu,
            $ram,
            $storage
        );

        return response()->json($result);
    }
}

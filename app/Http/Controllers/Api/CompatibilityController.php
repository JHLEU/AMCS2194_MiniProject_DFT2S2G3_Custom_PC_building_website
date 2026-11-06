<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CompatibilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CompatibilityController extends Controller
{
    public function __construct(
        private readonly CompatibilityService $compatibilityService
    ) {}

    /**
     * Evaluate a custom PC build for CPU <-> Motherboard socket compatibility and RAM compatibility.
     * Accepts `product_ids` (array of IDs) or `components` (map of category => product_id).
     */
    public function evaluate(Request $request): JsonResponse
    {
        $request->validate([
            'product_ids'   => ['nullable', 'array'],
            'product_ids.*' => ['nullable', 'integer'],
            'components'    => ['nullable', 'array'],
        ]);

        $productIds = [];

        if ($request->has('product_ids') && is_array($request->input('product_ids'))) {
            $productIds = array_values(array_filter(array_map('intval', $request->input('product_ids'))));
        } elseif ($request->has('components') && is_array($request->input('components'))) {
            foreach ($request->input('components') as $item) {
                if (is_numeric($item) && (int) $item > 0) {
                    $productIds[] = (int) $item;
                } elseif (is_array($item) && isset($item['id']) && is_numeric($item['id'])) {
                    $productIds[] = (int) $item['id'];
                }
            }
        }

        $result = $this->compatibilityService->evaluateBuild($productIds);

        return response()->json($result);
    }

    /**
     * Return catalog products annotated with compatibility status relative to currently selected parts.
     * Supports query params: `cpu_id`, `motherboard_id`, `ram_id`, `product_ids[]`, and optional `category` filter.
     */
    public function compatibleParts(Request $request): JsonResponse
    {
        $anchors = [];

        foreach (['cpu_id', 'motherboard_id', 'ram_id', 'gpu_id', 'storage_id', 'cooling_id', 'case_id'] as $key) {
            if ($request->filled($key) && is_numeric($request->query($key))) {
                $anchors[$key] = (int) $request->query($key);
            }
        }

        if ($request->has('product_ids') && is_array($request->query('product_ids'))) {
            foreach ($request->query('product_ids') as $idx => $id) {
                if (is_numeric($id) && (int) $id > 0) {
                    $anchors['p_' . $idx] = (int) $id;
                }
            }
        }

        $annotated = $this->compatibilityService->getAnnotatedCatalog($anchors);

        if ($request->filled('category')) {
            $categoryFilter = trim((string) $request->query('category'));
            $annotated = $annotated->filter(
                fn (array $item) => strcasecmp($item['category'], $categoryFilter) === 0
            )->values();
        }

        if ($request->boolean('only_compatible')) {
            $annotated = $annotated->where('compatible', true)->values();
        }

        return response()->json([
            'anchors' => array_values($anchors),
            'count'   => $annotated->count(),
            'parts'   => $annotated,
        ]);
    }

    /**
     * Calculate 4-parameter performance rating scores (CPU Processing Power, GPU Performance, Memory, Storage).
     */
    public function bottleneck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cpu_id'     => ['nullable', 'integer', 'exists:products,id'],
            'gpu_id'     => ['nullable', 'integer', 'exists:products,id'],
            'ram_id'     => ['nullable', 'integer', 'exists:products,id'],
            'storage_id' => ['nullable', 'integer', 'exists:products,id'],
            'cpu'        => ['nullable', 'array'],
            'gpu'        => ['nullable', 'array'],
            'ram'        => ['nullable', 'array'],
            'storage'    => ['nullable', 'array'],
        ]);

        $cpu = ! empty($validated['cpu_id'])
            ? Product::findOrFail($validated['cpu_id'])
            : ($validated['cpu'] ?? (array_key_exists('cpu_id', $validated) ? null : Product::where('category', 'CPU')->first()));

        $gpu = ! empty($validated['gpu_id'])
            ? Product::findOrFail($validated['gpu_id'])
            : ($validated['gpu'] ?? (array_key_exists('gpu_id', $validated) ? null : Product::where('category', 'GPU')->first()));

        $ram = ! empty($validated['ram_id'])
            ? Product::findOrFail($validated['ram_id'])
            : ($validated['ram'] ?? (array_key_exists('ram_id', $validated) ? null : Product::where('category', 'RAM')->first()));

        $storage = ! empty($validated['storage_id'])
            ? Product::findOrFail($validated['storage_id'])
            : ($validated['storage'] ?? (array_key_exists('storage_id', $validated) ? null : Product::where('category', 'Storage')->first()));

        $report = $this->compatibilityService->calculatePerformance($cpu, $gpu, $ram, $storage);

        return response()->json($report);
    }
}

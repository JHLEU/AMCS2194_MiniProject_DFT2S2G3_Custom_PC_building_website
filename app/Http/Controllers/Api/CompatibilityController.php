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
     * Evaluate a custom PC build for socket compatibility, RAM standard, TDP wattage, PSU headroom, and pricing.
     * Accepts `product_ids` (array of IDs) or `components` (map of category => product_id).
     */
    public function evaluate(Request $request): JsonResponse
    {
        $productIds = [];

        if ($request->has('product_ids') && is_array($request->input('product_ids'))) {
            $productIds = array_values(array_filter($request->input('product_ids')));
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
     * Calculate CPU + GPU bottleneck, synergy, recommended PSU wattage, cooling advice, and game FPS estimates.
     */
    public function bottleneck(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cpu_id'     => ['nullable', 'integer', 'exists:products,id'],
            'gpu_id'     => ['nullable', 'integer', 'exists:products,id'],
            'cpu'        => ['nullable', 'array'],
            'gpu'        => ['nullable', 'array'],
            'resolution' => ['nullable', 'string', 'in:1080,1440,2160'],
            'workload'   => ['nullable', 'string', 'in:game_3a,fps_comp,render_4k'],
        ]);

        $cpu = null;
        if (! empty($validated['cpu_id'])) {
            $cpu = Product::findOrFail($validated['cpu_id']);
        } elseif (! empty($validated['cpu'])) {
            $cpu = $validated['cpu'];
        } else {
            $cpu = Product::where('category', 'CPU')->first() ?? [
                'name'        => 'AMD Ryzen 7 7800X3D',
                'tdp_wattage' => 120,
                'score'       => 99,
            ];
        }

        $gpu = null;
        if (! empty($validated['gpu_id'])) {
            $gpu = Product::findOrFail($validated['gpu_id']);
        } elseif (! empty($validated['gpu'])) {
            $gpu = $validated['gpu'];
        } else {
            $gpu = Product::where('category', 'GPU')->first() ?? [
                'name'        => 'NVIDIA GeForce RTX 4070 Super 12GB',
                'tdp_wattage' => 220,
                'score'       => 84,
            ];
        }

        $resolution = $validated['resolution'] ?? '1440';
        $workload = $validated['workload'] ?? 'game_3a';

        $report = $this->compatibilityService->calculateBottleneck($cpu, $gpu, $resolution, $workload);

        return response()->json($report);
    }
}

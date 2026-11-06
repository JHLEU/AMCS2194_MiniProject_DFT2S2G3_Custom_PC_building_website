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

        return view('calculator.index', compact('cpus', 'gpus'));
    }

    public function calculate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'cpu_id'     => 'required|exists:products,id',
            'gpu_id'     => 'required|exists:products,id',
            'resolution' => 'nullable|in:1080p,1440p,4k,2160p',
        ]);

        $resolution = $validated['resolution'] ?? '1440p';
        if ($resolution === '2160p') {
            $resolution = '4k';
        }

        $cpu = Product::findOrFail((int) $validated['cpu_id']);
        $gpu = Product::findOrFail((int) $validated['gpu_id']);

        $resKey = str_replace('p', '', strtolower($resolution));

        $result = $this->compatibilityService->calculateBottleneck(
            $cpu,
            $gpu,
            $resKey
        );

        $synergyScore = (int) round($result['utilization_percent'] ?? (100 - ($result['bottleneck_percent'] ?? 5)));
        $fpsEstimates = [];
        if (!empty($result['games'])) {
            foreach ($result['games'] as $g) {
                $fpsEstimates[$g['name']] = $g['fps'] . ' FPS';
            }
        }

        return response()->json([
            'synergy_score'      => $synergyScore,
            'verdict'            => $result['tag'] ?? 'Optimal Hardware Balance',
            'bottleneck_percent' => $result['bottleneck_percent'] ?? 0,
            'advice'             => $result['advice'] ?? '',
            'fps_estimates'      => $fpsEstimates,
            'games'              => $result['games'] ?? [],
        ]);
    }
}

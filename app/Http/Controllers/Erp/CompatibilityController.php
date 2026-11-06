<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\CompatibilityService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CompatibilityController extends Controller
{
    public function __construct(
        private readonly CompatibilityService $compatibilityService
    ) {}

    /**
     * Render the ERP Hardware Compatibility, Power Headroom & Bottleneck Test Bench.
     */
    public function index(Request $request): View
    {
        $catalog = Product::withStockCounts()
            ->orderBy('category')
            ->orderBy('selling_price')
            ->get();

        $groupedProducts = $catalog->groupBy('category');

        $selectedIds = [];
        $categories = ['CPU', 'Motherboard', 'RAM', 'GPU', 'Storage', 'Cooling', 'PSU', 'Case'];

        foreach ($categories as $cat) {
            $paramKey = strtolower($cat) . '_id';
            if ($request->filled($paramKey) && is_numeric($request->input($paramKey))) {
                $selectedIds[$cat] = (int) $request->input($paramKey);
            }
        }

        // Default initial selection if no query parameters provided
        if (empty($selectedIds)) {
            foreach ($categories as $cat) {
                if (isset($groupedProducts[$cat]) && $groupedProducts[$cat]->isNotEmpty()) {
                    // Pick first item in each category by default
                    $selectedIds[$cat] = $groupedProducts[$cat]->first()->id;
                }
            }
        }

        $evaluation = $this->compatibilityService->evaluateBuild(array_values($selectedIds));

        $selectedCpu = isset($selectedIds['CPU']) ? $catalog->firstWhere('id', $selectedIds['CPU']) : null;
        $selectedGpu = isset($selectedIds['GPU']) ? $catalog->firstWhere('id', $selectedIds['GPU']) : null;
        $resolution = in_array($request->input('resolution'), ['1080', '1440', '2160'], true)
            ? $request->input('resolution')
            : '1440';

        $bottleneckReport = null;
        if ($selectedCpu && $selectedGpu) {
            $bottleneckReport = $this->compatibilityService->calculateBottleneck(
                $selectedCpu,
                $selectedGpu,
                $resolution
            );
        }

        return view('erp.compatibility.index', [
            'categories'       => $categories,
            'groupedProducts'  => $groupedProducts,
            'selectedIds'      => $selectedIds,
            'evaluation'       => $evaluation,
            'bottleneckReport' => $bottleneckReport,
            'resolution'       => $resolution,
        ]);
    }
}

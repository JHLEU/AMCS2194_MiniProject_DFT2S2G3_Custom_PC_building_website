<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Services\CompatibilityService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PcBuilderController extends Controller
{
    public function __construct(
        protected CompatibilityService $compatibilityService
    ) {}

    public function index(Request $request): View
    {
        // 8 Core Hardware Slots for PC Building
        $slotCategories = [
            'CPU'         => ['name' => 'Processor (CPU)', 'icon' => '🔲', 'desc' => 'Core computing brain of your PC'],
            'Motherboard' => ['name' => 'Motherboard', 'icon' => '🔌', 'desc' => 'Main circuit board connecting all hardware'],
            'RAM'         => ['name' => 'Memory (RAM)', 'icon' => '⚡', 'desc' => 'High-speed system memory for multitasking'],
            'GPU'         => ['name' => 'Graphics Card (GPU)', 'icon' => '🎮', 'desc' => 'High-performance visual rendering engine'],
            'Storage'     => ['name' => 'Primary Storage (SSD)', 'icon' => '💾', 'desc' => 'Lightning fast NVMe M.2 Solid State Drive'],
            'PSU'         => ['name' => 'Power Supply Unit (PSU)', 'icon' => '🔋', 'desc' => 'Clean & reliable electrical power distribution'],
            'Cooling'     => ['name' => 'Thermal Cooling', 'icon' => '❄️', 'desc' => 'Liquid AIO or High-Performance Air Cooler'],
            'Case'        => ['name' => 'PC Chassis / Case', 'icon' => '🖥️', 'desc' => 'Tempered glass airflow tower enclosure'],
        ];

        // Fetch active products grouped by category
        $catalog = Product::orderBy('selling_price', 'asc')
            ->get()
            ->groupBy('category');

        // Preselected items from query (e.g. from Bottleneck calculator or featured builds)
        $preselectedIds = [];
        if ($request->filled('cpu_id')) {
            $preselectedIds['CPU'] = (int) $request->input('cpu_id');
        }
        if ($request->filled('gpu_id')) {
            $preselectedIds['GPU'] = (int) $request->input('gpu_id');
        }
        if ($request->filled('product_ids')) {
            $ids = is_array($request->input('product_ids'))
                ? $request->input('product_ids')
                : explode(',', (string) $request->input('product_ids'));

            $prods = Product::whereIn('id', $ids)->get();
            foreach ($prods as $p) {
                $cat = $p->category;
                if (isset($slotCategories[$cat])) {
                    $preselectedIds[$cat] = $p->id;
                }
            }
        }

        return view('builder.index', compact('slotCategories', 'catalog', 'preselectedIds'));
    }

    public function buildToCart(Request $request): RedirectResponse
    {
        $productIds = $request->input('product_ids', []);

        if (is_string($productIds)) {
            $productIds = explode(',', $productIds);
        }

        $cleanIds = array_filter(array_map('intval', (array) $productIds));

        if (empty($cleanIds)) {
            return redirect()->route('builder.index')->with('error', 'Please select at least one component to proceed.');
        }

        return redirect()->route('checkout.index', ['product_ids' => $cleanIds]);
    }
}

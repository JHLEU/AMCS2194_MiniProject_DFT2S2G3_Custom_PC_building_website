<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;

class CompatibilityService
{
    /**
     * Base system power overhead (kept for informational metrics).
     */
    public const BASE_SYSTEM_WATTAGE = 60;

    /**
     * Recommended PSU headroom multiplier.
     */
    public const PSU_RECOMMENDED_MULTIPLIER = 1.25;

    /**
     * Standard retail PSU wattage tiers in watts.
     */
    public const PSU_TIERS = [550, 650, 750, 850, 1000, 1200, 1600];

    /**
     * Evaluate a custom PC build for hardware compatibility:
     * Only checks:
     * 1. Selected Motherboard socket_type vs selected CPU socket_type
     * 2. RAM compatibility (e.g., DDR4 motherboard with DDR5 memory selected is flagged)
     *
     * @param  iterable<Product|int|string>  $components
     * @return array<string, mixed>
     */
    public function evaluateBuild(iterable $components): array
    {
        $products = $this->resolveProducts($components);

        $errors = [];
        $warnings = [];

        // Group products by normalized uppercase category key for rule evaluation
        $byCategory = [];
        foreach ($products as $product) {
            $catKey = strtoupper(trim((string) $product->category));
            $byCategory[$catKey][] = $product;
        }

        /** @var Product|null $cpu */
        $cpu = $byCategory['CPU'][0] ?? null;
        /** @var Product|null $motherboard */
        $motherboard = $byCategory['MOTHERBOARD'][0] ?? null;
        /** @var array<int, Product> $rams */
        $rams = $byCategory['RAM'] ?? [];
        /** @var Product|null $gpu */
        $gpu = $byCategory['GPU'][0] ?? null;
        /** @var Product|null $storage */
        $storage = $byCategory['STORAGE'][0] ?? null;
        /** @var Product|null $psu */
        $psu = $byCategory['PSU'][0] ?? null;

        // 1. Socket Compatibility Check (CPU <-> Motherboard)
        if ($cpu && $motherboard) {
            $cpuSocket = $this->normalizeToken($cpu->socket_type);
            $mbSocket = $this->normalizeToken($motherboard->socket_type);

            if ($cpuSocket && $mbSocket) {
                if ($cpuSocket !== $mbSocket) {
                    $errors[] = [
                        'code'     => 'socket_mismatch',
                        'category' => 'Motherboard',
                        'message'  => sprintf(
                            'Socket mismatch: CPU "%s" requires socket %s, but Motherboard "%s" uses socket %s.',
                            $cpu->name,
                            $cpuSocket,
                            $motherboard->name,
                            $mbSocket
                        ),
                    ];
                }
            } else {
                $warnings[] = [
                    'code'     => 'socket_unspecified',
                    'category' => 'Motherboard',
                    'message'  => 'One of the selected CPU or Motherboard components does not specify a socket_type.',
                ];
            }
        }

        // 2. RAM Compatibility Check (Motherboard <-> CPU and Motherboard/CPU <-> RAM)
        if ($cpu && $motherboard) {
            $cpuRam = $this->normalizeToken($cpu->ram_type);
            $mbRam = $this->normalizeToken($motherboard->ram_type);

            if ($cpuRam && $mbRam && $cpuRam !== $mbRam) {
                $errors[] = [
                    'code'     => 'ram_mismatch',
                    'category' => 'Motherboard',
                    'message'  => sprintf(
                        'RAM type mismatch: CPU "%s" supports %s memory, but Motherboard "%s" requires %s.',
                        $cpu->name,
                        $cpuRam,
                        $motherboard->name,
                        $mbRam
                    ),
                ];
            }
        }

        foreach ($rams as $ram) {
            $ramType = $this->normalizeToken($ram->ram_type);

            if ($motherboard) {
                $mbRam = $this->normalizeToken($motherboard->ram_type);
                if ($ramType && $mbRam && $ramType !== $mbRam) {
                    $errors[] = [
                        'code'     => 'ram_mismatch',
                        'category' => 'RAM',
                        'message'  => sprintf(
                            'RAM type mismatch: Motherboard "%s" requires %s memory, but "%s" is %s.',
                            $motherboard->name,
                            $mbRam,
                            $ram->name,
                            $ramType
                        ),
                    ];
                } elseif (! $ramType || ! $mbRam) {
                    $warnings[] = [
                        'code'     => 'ram_unspecified',
                        'category' => 'RAM',
                        'message'  => 'RAM generation (DDR4/DDR5) is not fully specified on the selected Motherboard or RAM.',
                    ];
                }
            }

            if ($cpu) {
                $cpuRam = $this->normalizeToken($cpu->ram_type);
                if ($ramType && $cpuRam && $ramType !== $cpuRam) {
                    $alreadyFlagged = collect($errors)->contains('code', 'ram_mismatch');
                    if (! $alreadyFlagged) {
                        $errors[] = [
                            'code'     => 'ram_mismatch',
                            'category' => 'RAM',
                            'message'  => sprintf(
                                'RAM type mismatch: CPU "%s" supports %s memory, but "%s" is %s.',
                                $cpu->name,
                                $cpuRam,
                                $ram->name,
                                $ramType
                            ),
                        ];
                    }
                }
            }
        }

        // Pricing & Stock Availability Summary
        $estimatedPrice = 0.0;
        $allInStock = $products->isNotEmpty();
        $componentTdp = 0;

        if ($products->contains(fn (Product $p) => $p->getAttribute('in_stock_count') === null)) {
            (new EloquentCollection($products->all()))->loadCount([
                'serialNumbers as in_stock_count' => fn ($q) => $q->where('status', 'In-Stock'),
            ]);
        }

        $selectedPartsSummary = [];
        foreach ($products as $product) {
            $price = (float) $product->selling_price;
            $estimatedPrice += $price;

            if (! $this->isPsu($product)) {
                $componentTdp += max(0, (int) $product->tdp_wattage);
            }

            $inStockCount = $product->getAttribute('in_stock_count');
            if ($inStockCount === null) {
                $inStockCount = $product->serialNumbers()
                    ->where('status', 'In-Stock')
                    ->count();
            }

            if ((int) $inStockCount <= 0) {
                $allInStock = false;
            }

            $selectedPartsSummary[] = [
                'id'                => $product->id,
                'name'              => $product->name,
                'category'          => $product->category,
                'brand'             => $product->brand,
                'socket_type'       => $product->socket_type,
                'ram_type'          => $product->ram_type,
                'tdp_wattage'       => (int) $product->tdp_wattage,
                'performance_score' => $this->estimatePerformanceScore($product, (string) $product->category),
                'selling_price'     => round($price, 2),
                'in_stock_count'    => (int) $inStockCount,
            ];
        }

        $baseOverhead = $products->isEmpty() ? 0 : self::BASE_SYSTEM_WATTAGE;
        $estimatedTotalWattage = $componentTdp + $baseOverhead;
        $recommendedPsuWattage = $this->calculateRecommendedPsuWattage($estimatedTotalWattage);
        $selectedPsuWattage = $psu ? $this->extractPsuCapacityWatts($psu) : null;

        $compatible = count($errors) === 0;
        $status = ! $compatible
            ? 'incompatible'
            : (count($warnings) > 0 ? 'warning' : 'compatible');

        $performance = $this->calculatePerformance($cpu, $gpu, $rams[0] ?? null, $storage);

        return [
            'compatible'    => $compatible,
            'is_compatible' => $compatible,
            'status'        => $status,
            'errors'        => $errors,
            'warnings'      => $warnings,
            'performance'   => $performance,
            'power'         => [
                'component_tdp_wattage'   => $componentTdp,
                'base_overhead_wattage'   => $baseOverhead,
                'estimated_total_wattage' => $estimatedTotalWattage,
                'recommended_psu_wattage' => $recommendedPsuWattage,
                'selected_psu_wattage'    => $selectedPsuWattage,
                'psu_headroom_watts'      => $selectedPsuWattage !== null ? ($selectedPsuWattage - $estimatedTotalWattage) : null,
                'psu_headroom_percent'    => ($selectedPsuWattage !== null && $estimatedTotalWattage > 0)
                    ? round((($selectedPsuWattage - $estimatedTotalWattage) / $estimatedTotalWattage) * 100, 1)
                    : null,
                'psu_load_percent'        => ($selectedPsuWattage !== null && $selectedPsuWattage > 0)
                    ? round(($estimatedTotalWattage / $selectedPsuWattage) * 100, 1)
                    : null,
                'psu_status'              => $selectedPsuWattage ? 'ok' : 'none',
            ],
            'pricing' => [
                'estimated_total_price' => round($estimatedPrice, 2),
                'component_count'       => $products->count(),
                'all_in_stock'          => $allInStock,
            ],
            'components' => $selectedPartsSummary,
        ];
    }

    /**
     * Annotate all catalog products with compatibility status relative to currently selected anchor components.
     * Only checks CPU <-> Motherboard socket compatibility and Motherboard/CPU <-> RAM compatibility.
     *
     * @param  array<string, int|Product|null>  $anchorSelection
     * @return Collection<int, array<string, mixed>>
     */
    public function getAnnotatedCatalog(array $anchorSelection = []): Collection
    {
        $anchorProducts = $this->resolveProducts(array_values(array_filter($anchorSelection)));

        /** @var Product|null $selectedCpu */
        $selectedCpu = $anchorProducts->first(fn (Product $p) => strcasecmp($p->category, 'CPU') === 0);
        /** @var Product|null $selectedMb */
        $selectedMb = $anchorProducts->first(fn (Product $p) => strcasecmp($p->category, 'Motherboard') === 0);
        /** @var Product|null $selectedRam */
        $selectedRam = $anchorProducts->first(fn (Product $p) => strcasecmp($p->category, 'RAM') === 0);

        $catalog = Product::withStockCounts()
            ->orderBy('category')
            ->orderBy('selling_price')
            ->get();

        return $catalog->map(function (Product $candidate) use ($selectedCpu, $selectedMb, $selectedRam) {
            $reasons = [];
            $cat = strtoupper(trim((string) $candidate->category));

            if ($cat === 'MOTHERBOARD') {
                if ($selectedCpu) {
                    $cpuSocket = $this->normalizeToken($selectedCpu->socket_type);
                    $mbSocket = $this->normalizeToken($candidate->socket_type);
                    if ($cpuSocket && $mbSocket && $cpuSocket !== $mbSocket) {
                        $reasons[] = "Socket {$mbSocket} is incompatible with selected CPU ({$cpuSocket}).";
                    }

                    $cpuRam = $this->normalizeToken($selectedCpu->ram_type);
                    $mbRam = $this->normalizeToken($candidate->ram_type);
                    if ($cpuRam && $mbRam && $cpuRam !== $mbRam) {
                        $reasons[] = "Motherboard supports {$mbRam}, but selected CPU requires {$cpuRam}.";
                    }
                }
                if ($selectedRam) {
                    $ramType = $this->normalizeToken($selectedRam->ram_type);
                    $mbRam = $this->normalizeToken($candidate->ram_type);
                    if ($ramType && $mbRam && $ramType !== $mbRam) {
                        $reasons[] = "Motherboard supports {$mbRam}, but selected RAM is {$ramType}.";
                    }
                }
            } elseif ($cat === 'CPU') {
                if ($selectedMb) {
                    $mbSocket = $this->normalizeToken($selectedMb->socket_type);
                    $cpuSocket = $this->normalizeToken($candidate->socket_type);
                    if ($mbSocket && $cpuSocket && $mbSocket !== $cpuSocket) {
                        $reasons[] = "Socket {$cpuSocket} is incompatible with selected Motherboard ({$mbSocket}).";
                    }

                    $mbRam = $this->normalizeToken($selectedMb->ram_type);
                    $cpuRam = $this->normalizeToken($candidate->ram_type);
                    if ($mbRam && $cpuRam && $mbRam !== $cpuRam) {
                        $reasons[] = "CPU supports {$cpuRam}, but selected Motherboard requires {$mbRam}.";
                    }
                }
                if ($selectedRam) {
                    $ramType = $this->normalizeToken($selectedRam->ram_type);
                    $cpuRam = $this->normalizeToken($candidate->ram_type);
                    if ($ramType && $cpuRam && $ramType !== $cpuRam) {
                        $reasons[] = "CPU supports {$cpuRam}, but selected RAM is {$ramType}.";
                    }
                }
            } elseif ($cat === 'RAM') {
                $candRam = $this->normalizeToken($candidate->ram_type);
                if ($selectedMb) {
                    $mbRam = $this->normalizeToken($selectedMb->ram_type);
                    if ($candRam && $mbRam && $candRam !== $mbRam) {
                        $reasons[] = "RAM is {$candRam}, but selected Motherboard requires {$mbRam}.";
                    }
                }
                if ($selectedCpu && empty($reasons)) {
                    $cpuRam = $this->normalizeToken($selectedCpu->ram_type);
                    if ($candRam && $cpuRam && $candRam !== $cpuRam) {
                        $reasons[] = "RAM is {$candRam}, but selected CPU requires {$cpuRam}.";
                    }
                }
            }

            return [
                'id'                      => $candidate->id,
                'name'                    => $candidate->name,
                'category'                => $candidate->category,
                'brand'                   => $candidate->brand,
                'specs'                   => $candidate->specs,
                'socket_type'             => $candidate->socket_type,
                'ram_type'                => $candidate->ram_type,
                'tdp_wattage'             => (int) $candidate->tdp_wattage,
                'performance_score'       => $this->estimatePerformanceScore($candidate, (string) $candidate->category),
                'selling_price'           => (float) $candidate->selling_price,
                'in_stock_count'          => (int) ($candidate->in_stock_count ?? 0),
                'compatible'              => count($reasons) === 0,
                'incompatibility_reasons' => $reasons,
            ];
        });
    }

    /**
     * Calculate simplified 4-parameter system performance scores:
     * 1. CPU Processing Power
     * 2. GPU Performance
     * 3. Memory (RAM)
     * 4. Storage
     *
     * @param  Product|array<string, mixed>|null  $cpu
     * @param  Product|array<string, mixed>|null  $gpu
     * @param  Product|array<string, mixed>|null  $ram
     * @param  Product|array<string, mixed>|null  $storage
     * @return array<string, mixed>
     */
    public function calculatePerformance(
        Product|array|null $cpu = null,
        Product|array|null $gpu = null,
        Product|array|null $ram = null,
        Product|array|null $storage = null
    ): array {
        $cpuName = $this->extractName($cpu, 'CPU');
        $gpuName = $this->extractName($gpu, 'GPU');
        $ramName = $this->extractName($ram, 'RAM');
        $storageName = $this->extractName($storage, 'Storage');

        $cpuScore = $cpu ? $this->estimatePerformanceScore($cpu, 'CPU') : 0;
        $gpuScore = $gpu ? $this->estimatePerformanceScore($gpu, 'GPU') : 0;
        $ramScore = $ram ? $this->estimatePerformanceScore($ram, 'RAM') : 0;
        $storageScore = $storage ? $this->estimatePerformanceScore($storage, 'Storage') : 0;

        $activeScores = array_filter([$cpuScore, $gpuScore, $ramScore, $storageScore], fn (int $s) => $s > 0);
        $overallScore = count($activeScores) > 0
            ? (int) round(array_sum($activeScores) / count($activeScores))
            : 0;

        $cpuTier = $this->getPerformanceTier($cpuScore);
        $gpuTier = $this->getPerformanceTier($gpuScore);
        $ramTier = $this->getPerformanceTier($ramScore);
        $storageTier = $this->getPerformanceTier($storageScore);
        $overallTier = $this->getPerformanceTier($overallScore);

        if ($overallScore >= 85) {
            $verdict = 'Excellent Performance';
            $advice = 'All four core subsystems deliver flagship-tier processing, rendering, memory bandwidth, and storage throughput.';
        } elseif ($overallScore >= 70) {
            $verdict = 'Good Performance';
            $advice = 'Strong overall hardware ratings across CPU processing power, GPU rendering, system memory, and storage speed.';
        } elseif ($overallScore >= 50) {
            $verdict = 'Average Performance';
            $advice = 'Solid everyday and mainstream gaming configuration. Upgrading lower-scored components will boost responsiveness.';
        } else {
            $verdict = 'Poor / Entry Performance';
            $advice = 'Select or upgrade core components to achieve higher processing, graphics, memory, and storage performance.';
        }

        $parameters = [
            'cpu_processing_power' => [
                'key'        => 'cpu_processing_power',
                'label'      => 'CPU Processing Power',
                'category'   => 'CPU',
                'name'       => $cpuName,
                'score'      => $cpuScore,
                'tier'       => $cpuTier['tier'],
                'tier_label' => $cpuTier['label'],
                'color'      => $cpuTier['color'],
            ],
            'gpu_performance' => [
                'key'        => 'gpu_performance',
                'label'      => 'GPU Performance',
                'category'   => 'GPU',
                'name'       => $gpuName,
                'score'      => $gpuScore,
                'tier'       => $gpuTier['tier'],
                'tier_label' => $gpuTier['label'],
                'color'      => $gpuTier['color'],
            ],
            'memory' => [
                'key'        => 'memory',
                'label'      => 'Memory',
                'category'   => 'RAM',
                'name'       => $ramName,
                'score'      => $ramScore,
                'tier'       => $ramTier['tier'],
                'tier_label' => $ramTier['label'],
                'color'      => $ramTier['color'],
            ],
            'storage' => [
                'key'        => 'storage',
                'label'      => 'Storage',
                'category'   => 'Storage',
                'name'       => $storageName,
                'score'      => $storageScore,
                'tier'       => $storageTier['tier'],
                'tier_label' => $storageTier['label'],
                'color'      => $storageTier['color'],
            ],
        ];

        return [
            'overall_score'        => $overallScore,
            'overall_tier'         => $overallTier['tier'],
            'overall_tier_label'   => $overallTier['label'],
            'overall_color'        => $overallTier['color'],
            'synergy_score'        => $overallScore,
            'verdict'              => $verdict,
            'advice'               => $advice,
            'cpu_name'             => $cpuName,
            'gpu_name'             => $gpuName,
            'ram_name'             => $ramName,
            'storage_name'         => $storageName,
            'cpu_processing_power' => $cpuScore,
            'gpu_performance'      => $gpuScore,
            'memory'               => $ramScore,
            'storage'              => $storageScore,
            'cpu_score'            => $cpuScore,
            'gpu_score'            => $gpuScore,
            'memory_score'         => $ramScore,
            'storage_score'        => $storageScore,
            'parameters'           => $parameters,
        ];
    }

    /**
     * Map a 0-100 performance score into one of 4 tiers:
     * - Poor (Red: #ef4444)
     * - Average (Yellow: #f59e0b)
     * - Good (Green: #10b981)
     * - Excellent (Cyan: #06b6d4)
     *
     * @return array{tier: string, label: string, color: string}
     */
    public function getPerformanceTier(int $score): array
    {
        if ($score <= 0) {
            return [
                'tier'  => 'none',
                'label' => 'Not Selected',
                'color' => '#64748b',
            ];
        }

        if ($score >= 85) {
            return [
                'tier'  => 'excellent',
                'label' => 'Excellent',
                'color' => '#06b6d4', // Cyan
            ];
        }

        if ($score >= 70) {
            return [
                'tier'  => 'good',
                'label' => 'Good',
                'color' => '#10b981', // Green
            ];
        }

        if ($score >= 50) {
            return [
                'tier'  => 'average',
                'label' => 'Average',
                'color' => '#f59e0b', // Yellow
            ];
        }

        return [
            'tier'  => 'poor',
            'label' => 'Poor',
            'color' => '#ef4444', // Red
        ];
    }

    /**
     * Legacy/shared method for bottleneck/performance endpoint compatibility.
     *
     * @param  Product|array<string, mixed>  $cpu
     * @param  Product|array<string, mixed>  $gpu
     * @param  string  $resolution
     * @param  string  $workload
     * @param  Product|array<string, mixed>|null  $ram
     * @param  Product|array<string, mixed>|null  $storage
     * @return array<string, mixed>
     */
    public function calculateBottleneck(
        Product|array $cpu,
        Product|array $gpu,
        string $resolution = '1440',
        string $workload = 'game_3a',
        Product|array|null $ram = null,
        Product|array|null $storage = null
    ): array {
        $ram ??= Product::where('category', 'RAM')->orderBy('selling_price', 'desc')->first();
        $storage ??= Product::where('category', 'Storage')->orderBy('selling_price', 'desc')->first();

        $perf = $this->calculatePerformance($cpu, $gpu, $ram, $storage);

        return array_merge($perf, [
            'resolution'          => $resolution,
            'workload'            => $workload,
            'bottleneck_percent'  => max(0, abs($perf['cpu_score'] - $perf['gpu_score'])),
            'utilization_percent' => $perf['overall_score'],
            'tier'                => $perf['overall_score'] >= 85 ? 'optimal' : 'minor',
            'tag'                 => $perf['verdict'],
        ]);
    }

    /**
     * Get the 1-100 performance rating score for a component (CPU, GPU, RAM, Storage).
     * Uses the stored `performance_score` attribute on the Product model first.
     *
     * @param  Product|array<string, mixed>  $component
     */
    public function estimatePerformanceScore(Product|array $component, string $type = 'CPU'): int
    {
        if ($component instanceof Product && (int) $component->performance_score > 0) {
            return min(100, max(1, (int) $component->performance_score));
        }

        if (is_array($component)) {
            if (isset($component['performance_score']) && (int) $component['performance_score'] > 0) {
                return min(100, max(1, (int) $component['performance_score']));
            }
            if (isset($component['score']) && (int) $component['score'] > 0) {
                return min(100, max(1, (int) $component['score']));
            }
        }

        $name = strtoupper($component instanceof Product ? $component->name : (string) ($component['name'] ?? ''));
        $price = (float) ($component instanceof Product ? $component->selling_price : ($component['selling_price'] ?? $component['price'] ?? 0));
        $cat = strtoupper(trim($component instanceof Product ? (string) $component->category : $type));

        $knownScores = [
            '14900K'      => 99,
            '7800X3D'     => 98,
            '9800X3D'     => 100,
            '7950X'       => 98,
            '14700K'      => 95,
            '13700K'      => 92,
            '14600K'      => 88,
            '7700X'       => 88,
            '7600X'       => 84,
            '4090'        => 100,
            '4080 SUPER'  => 98,
            '4080'        => 96,
            '4070 TI'     => 91,
            '4070 SUPER'  => 88,
            '4070'        => 82,
            '990 PRO'     => 96,
            'VENGEANCE'   => 92,
            'FURY BEAST'  => 75,
        ];

        foreach ($knownScores as $keyword => $score) {
            if (str_contains($name, $keyword)) {
                return $score;
            }
        }

        if (! in_array($cat, ['CPU', 'GPU', 'RAM', 'STORAGE'], true)) {
            return 0;
        }

        return max(50, min(99, (int) round(60 + ($price * 0.06))));
    }

    public function calculateRecommendedPsuWattage(int $estimatedTotalWattage): int
    {
        if ($estimatedTotalWattage <= 0) {
            return 550;
        }

        $targetWithHeadroom = (int) ceil($estimatedTotalWattage * self::PSU_RECOMMENDED_MULTIPLIER);

        foreach (self::PSU_TIERS as $tier) {
            if ($tier >= $targetWithHeadroom) {
                return $tier;
            }
        }

        return (int) (ceil($targetWithHeadroom / 100) * 100);
    }

    public function extractPsuCapacityWatts(Product $psu): int
    {
        $tdpColumn = (int) $psu->tdp_wattage;
        if ($tdpColumn >= 300) {
            return $tdpColumn;
        }

        $haystack = $psu->name . ' ' . ($psu->specs ?? '');
        if (preg_match('/(\d{3,4})\s*W/i', $haystack, $matches)) {
            return (int) $matches[1];
        }

        return max(0, $tdpColumn);
    }

    /**
     * Resolve an iterable of Product models or Product IDs into a Collection of Product models.
     *
     * @param  iterable<Product|int|string|null>  $components
     * @return Collection<int, Product>
     */
    private function resolveProducts(iterable $components): Collection
    {
        $models = collect();
        $idsToFetch = [];

        foreach ($components as $item) {
            if ($item instanceof Product) {
                $models->push($item);
            } elseif (is_numeric($item) && (int) $item > 0) {
                $idsToFetch[] = (int) $item;
            } elseif (is_array($item) && isset($item['id']) && is_numeric($item['id'])) {
                $idsToFetch[] = (int) $item['id'];
            }
        }

        if (! empty($idsToFetch)) {
            $fetched = Product::withStockCounts()
                ->whereIn('id', array_unique($idsToFetch))
                ->get();
            $models = $models->concat($fetched);
        }

        return $models->unique('id')->values();
    }

    private function extractName(Product|array|null $component, string $fallback): string
    {
        if ($component instanceof Product) {
            return $component->name;
        }
        if (is_array($component) && ! empty($component['name'])) {
            return (string) $component['name'];
        }

        return $fallback;
    }

    private function isPsu(Product $product): bool
    {
        return strcasecmp(trim((string) $product->category), 'PSU') === 0;
    }

    private function normalizeToken(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = strtoupper(trim($value));

        return $trimmed !== '' ? $trimmed : null;
    }
}

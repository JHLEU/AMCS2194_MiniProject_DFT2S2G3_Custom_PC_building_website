<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class CompatibilityService
{
    /**
     * Base system power overhead (fans, RGB controllers, VRM losses, USB devices, NVMe/SATA controllers).
     */
    public const BASE_SYSTEM_WATTAGE = 60;

    /**
     * Recommended PSU headroom multiplier (1.25x = 25% safety margin above peak load).
     */
    public const PSU_RECOMMENDED_MULTIPLIER = 1.25;

    /**
     * Minimum safe PSU multiplier before emitting a tight headroom warning (1.15x = 15% margin).
     */
    public const PSU_MIN_SAFE_MULTIPLIER = 1.15;

    /**
     * Standard retail PSU wattage tiers in watts.
     */
    public const PSU_TIERS = [550, 650, 750, 850, 1000, 1200, 1600];

    /**
     * Evaluate a custom PC build for hardware compatibility, TDP wattage, PSU headroom, cooling, and pricing.
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
        /** @var Product|null $psu */
        $psu = $byCategory['PSU'][0] ?? null;
        /** @var Product|null $cooler */
        $cooler = $byCategory['COOLING'][0] ?? null;

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

        // 2. CPU <-> Motherboard RAM Support Check (if both specify a RAM standard)
        if ($cpu && $motherboard) {
            $cpuRam = $this->normalizeToken($cpu->ram_type);
            $mbRam = $this->normalizeToken($motherboard->ram_type);

            if ($cpuRam && $mbRam && $cpuRam !== $mbRam) {
                $errors[] = [
                    'code'     => 'cpu_motherboard_ram_mismatch',
                    'category' => 'Motherboard',
                    'message'  => sprintf(
                        'Memory controller mismatch: CPU "%s" supports %s, but Motherboard "%s" is configured for %s.',
                        $cpu->name,
                        $cpuRam,
                        $motherboard->name,
                        $mbRam
                    ),
                ];
            }
        }

        // 3. RAM Compatibility Check (Motherboard <-> RAM and CPU <-> RAM)
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
                    // Avoid duplicate message if motherboard already flagged the exact same RAM mismatch,
                    // or include if motherboard wasn't selected yet
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

        // 4. TDP Wattage & Power Headroom Calculation
        $componentTdp = 0;
        foreach ($products as $product) {
            if ($this->isPsu($product)) {
                continue; // PSU tdp_wattage represents capacity supplied, not consumed
            }
            $componentTdp += max(0, (int) $product->tdp_wattage);
        }

        $baseOverhead = $products->isEmpty() ? 0 : self::BASE_SYSTEM_WATTAGE;
        $estimatedTotalWattage = $componentTdp + $baseOverhead;
        $recommendedPsuWattage = $this->calculateRecommendedPsuWattage($estimatedTotalWattage);

        $selectedPsuWattage = $psu ? $this->extractPsuCapacityWatts($psu) : null;
        $psuHeadroomWatts = null;
        $psuHeadroomPercent = null;
        $psuLoadPercent = null;
        $psuStatus = 'none';

        if ($psu && $selectedPsuWattage !== null && $selectedPsuWattage > 0) {
            $psuHeadroomWatts = $selectedPsuWattage - $estimatedTotalWattage;
            $psuHeadroomPercent = $estimatedTotalWattage > 0
                ? round((($selectedPsuWattage - $estimatedTotalWattage) / $estimatedTotalWattage) * 100, 1)
                : 100.0;
            $psuLoadPercent = round(($estimatedTotalWattage / $selectedPsuWattage) * 100, 1);

            $minSafeWattage = (int) ceil($estimatedTotalWattage * self::PSU_MIN_SAFE_MULTIPLIER);

            if ($selectedPsuWattage < $estimatedTotalWattage) {
                $psuStatus = 'insufficient';
                $errors[] = [
                    'code'     => 'psu_insufficient',
                    'category' => 'PSU',
                    'message'  => sprintf(
                        'Insufficient PSU wattage: Estimated system peak load is %dW, which exceeds "%s" rated capacity (%dW). Recommended PSU: %dW+.',
                        $estimatedTotalWattage,
                        $psu->name,
                        $selectedPsuWattage,
                        $recommendedPsuWattage
                    ),
                ];
            } elseif ($selectedPsuWattage < $minSafeWattage) {
                $psuStatus = 'tight';
                $warnings[] = [
                    'code'     => 'psu_tight_headroom',
                    'category' => 'PSU',
                    'message'  => sprintf(
                        'Tight PSU headroom: Estimated load is %dW on a %dW PSU (%.1f%% load). We recommend at least %dW for safe transient power spikes.',
                        $estimatedTotalWattage,
                        $selectedPsuWattage,
                        $psuLoadPercent,
                        $recommendedPsuWattage
                    ),
                ];
            } else {
                $psuStatus = 'ok';
            }
        }

        // 5. Thermal & Cooling Check
        $cpuTdp = $cpu ? (int) $cpu->tdp_wattage : 0;
        $coolingAdvice = $this->generateCoolingAdvice($cpu);

        if ($cpu && $cooler && $cpuTdp >= 200) {
            $coolerTdpMetric = (int) $cooler->tdp_wattage;
            $isAio360 = str_contains(strtolower($cooler->name . ' ' . $cooler->specs), '360');
            if ($coolerTdpMetric < 25 && ! $isAio360) {
                $warnings[] = [
                    'code'     => 'cooling_underpowered',
                    'category' => 'Cooling',
                    'message'  => sprintf(
                        'Thermal warning: "%s" has a high peak TDP (%dW). A 360mm AIO liquid cooler is strongly recommended over "%s" to prevent thermal throttling.',
                        $cpu->name,
                        $cpuTdp,
                        $cooler->name
                    ),
                ];
            }
        }

        // 6. Pricing & Stock Availability Summary
        $estimatedPrice = 0.0;
        $allInStock = $products->isNotEmpty();

        $selectedPartsSummary = [];
        foreach ($products as $product) {
            $price = (float) $product->selling_price;
            $estimatedPrice += $price;

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
                'id'             => $product->id,
                'name'           => $product->name,
                'category'       => $product->category,
                'brand'          => $product->brand,
                'socket_type'    => $product->socket_type,
                'ram_type'       => $product->ram_type,
                'tdp_wattage'    => (int) $product->tdp_wattage,
                'selling_price'  => round($price, 2),
                'in_stock_count' => (int) $inStockCount,
            ];
        }

        $compatible = count($errors) === 0;
        $status = ! $compatible
            ? 'incompatible'
            : (count($warnings) > 0 ? 'warning' : 'compatible');

        return [
            'compatible' => $compatible,
            'status'     => $status,
            'errors'     => $errors,
            'warnings'   => $warnings,
            'power'      => [
                'component_tdp_wattage'   => $componentTdp,
                'base_overhead_wattage'   => $baseOverhead,
                'estimated_total_wattage' => $estimatedTotalWattage,
                'recommended_psu_wattage' => $recommendedPsuWattage,
                'selected_psu_wattage'    => $selectedPsuWattage,
                'psu_headroom_watts'      => $psuHeadroomWatts,
                'psu_headroom_percent'    => $psuHeadroomPercent,
                'psu_load_percent'        => $psuLoadPercent,
                'psu_status'              => $psuStatus,
            ],
            'cooling_advice' => $coolingAdvice,
            'pricing'        => [
                'estimated_total_price' => round($estimatedPrice, 2),
                'component_count'       => $products->count(),
                'all_in_stock'          => $allInStock,
            ],
            'components' => $selectedPartsSummary,
        ];
    }

    /**
     * Annotate all catalog products with compatibility status relative to currently selected anchor components.
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

        // Compute current non-PSU wattage to evaluate candidate PSUs
        $nonPsuProducts = $anchorProducts->reject(fn (Product $p) => $this->isPsu($p));
        $currentEstimatedWattage = $nonPsuProducts->isEmpty()
            ? 0
            : ($nonPsuProducts->sum(fn (Product $p) => max(0, (int) $p->tdp_wattage)) + self::BASE_SYSTEM_WATTAGE);

        $catalog = Product::withStockCounts()
            ->orderBy('category')
            ->orderBy('selling_price')
            ->get();

        return $catalog->map(function (Product $candidate) use ($selectedCpu, $selectedMb, $selectedRam, $currentEstimatedWattage) {
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
                        $reasons[] = "RAM type {$mbRam} conflicts with selected CPU ({$cpuRam}).";
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
                        $reasons[] = "CPU memory type {$cpuRam} conflicts with selected Motherboard ({$mbRam}).";
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
            } elseif ($cat === 'PSU' && $currentEstimatedWattage > 0) {
                $psuWatts = $this->extractPsuCapacityWatts($candidate);
                if ($psuWatts > 0 && $psuWatts < $currentEstimatedWattage) {
                    $reasons[] = "Rated capacity ({$psuWatts}W) is below estimated system load ({$currentEstimatedWattage}W).";
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
                'selling_price'           => (float) $candidate->selling_price,
                'in_stock_count'          => (int) ($candidate->in_stock_count ?? 0),
                'compatible'              => count($reasons) === 0,
                'incompatibility_reasons' => $reasons,
            ];
        });
    }

    /**
     * Calculate CPU + GPU bottleneck, synergy, power requirement, and estimated game FPS.
     *
     * @param  Product|array<string, mixed>  $cpu
     * @param  Product|array<string, mixed>  $gpu
     * @param  string  $resolution  '1080' | '1440' | '2160'
     * @param  string  $workload    'game_3a' | 'fps_comp' | 'render_4k'
     * @return array<string, mixed>
     */
    public function calculateBottleneck(
        Product|array $cpu,
        Product|array $gpu,
        string $resolution = '1440',
        string $workload = 'game_3a'
    ): array {
        $cpuName = $cpu instanceof Product ? $cpu->name : (string) ($cpu['name'] ?? 'CPU');
        $gpuName = $gpu instanceof Product ? $gpu->name : (string) ($gpu['name'] ?? 'GPU');

        $cpuTdp = $cpu instanceof Product ? (int) $cpu->tdp_wattage : (int) ($cpu['tdp_wattage'] ?? $cpu['tdp'] ?? 120);
        $gpuTdp = $gpu instanceof Product ? (int) $gpu->tdp_wattage : (int) ($gpu['tdp_wattage'] ?? $gpu['tdp'] ?? 220);

        $cpuScore = $this->estimatePerformanceScore($cpu, 'CPU');
        $gpuScore = $this->estimatePerformanceScore($gpu, 'GPU');

        // System wattage for CPU + GPU + standard system base (70W)
        $totalWattage = $cpuTdp + $gpuTdp + 70;
        $recommendedPsu = $this->calculateRecommendedPsuWattage($totalWattage);

        // Resolution-weighted bottleneck calculation
        $diff = abs($cpuScore - $gpuScore);
        if ($resolution === '1080' && $cpuScore < $gpuScore) {
            $diff += 6;
        } elseif ($resolution === '2160' && $gpuScore < $cpuScore) {
            $diff += 4;
        }

        if ($workload === 'fps_comp' && $cpuScore < $gpuScore) {
            $diff += 3;
        } elseif ($workload === 'render_4k' && $cpuScore < $gpuScore) {
            $diff += 4;
        }

        $bottleneckPercent = round(min(28.0, max(1.8, $diff * 0.95)), 1);
        $utilizationPercent = round(100.0 - $bottleneckPercent, 1);

        if ($bottleneckPercent < 6.0) {
            $tier = 'optimal';
            $tag = 'Optimal Synergy (Balanced)';
            $advice = sprintf(
                '%s and %s are ideally balanced at %sp. Both components can operate above %.1f%% theoretical potential without bottlenecking.',
                $cpuName,
                $gpuName,
                $resolution,
                $utilizationPercent
            );
        } elseif ($bottleneckPercent < 14.0) {
            $tier = 'minor';
            $tag = 'Minor Bottleneck (Recommended)';
            $limitingPart = $cpuScore < $gpuScore ? $cpuName : $gpuName;
            $advice = sprintf(
                'There is a minor %.1f%% bottleneck leaning toward %s at %sp, which is negligible in real-world gaming and productivity.',
                $bottleneckPercent,
                $limitingPart,
                $resolution
            );
        } else {
            $tier = 'noticeable';
            $tag = 'Performance Bottleneck Detected';
            $limitingPart = $cpuScore < $gpuScore ? $cpuName : $gpuName;
            $advice = sprintf(
                'A %.1f%% performance gap was detected (%s is limiting peak output at %sp). Consider pairing a closer performance tier.',
                $bottleneckPercent,
                $limitingPart,
                $resolution
            );
        }

        $coolingAdvice = $cpu instanceof Product
            ? $this->generateCoolingAdvice($cpu)
            : $this->generateCoolingAdviceFromTdp($cpuTdp, $cpuName);

        // Estimate average FPS across benchmark titles
        $multiplier = match ($resolution) {
            '1080'  => 1.35,
            '2160'  => 0.62,
            default => 1.0,
        };

        $games = [
            [
                'name' => 'Cyberpunk 2077 (RT Ultra)',
                'tag'  => 'AAA Ray Tracing',
                'fps'  => (int) round(($gpuScore * 0.95 + $cpuScore * 0.2) * $multiplier * 0.9),
            ],
            [
                'name' => 'Black Myth: Wukong (High)',
                'tag'  => 'Unreal Engine 5',
                'fps'  => (int) round(($gpuScore * 0.9 + $cpuScore * 0.25) * $multiplier * 0.95),
            ],
            [
                'name' => 'Valorant',
                'tag'  => 'Competitive Esports',
                'fps'  => (int) round(($cpuScore * 2.8 + $gpuScore * 1.5) * ($resolution === '2160' ? 0.8 : 1.2)),
            ],
            [
                'name' => 'Counter-Strike 2',
                'tag'  => 'Tactical Shooter',
                'fps'  => (int) round(($cpuScore * 2.0 + $gpuScore * 1.2) * $multiplier),
            ],
            [
                'name' => 'Apex Legends',
                'tag'  => 'Battle Royale',
                'fps'  => (int) round(($gpuScore * 1.4 + $cpuScore * 0.4) * $multiplier),
            ],
            [
                'name' => 'GTA V / Open World Ultra',
                'tag'  => 'Open World',
                'fps'  => (int) round(($gpuScore * 1.2 + $cpuScore * 0.5) * $multiplier),
            ],
        ];

        return [
            'cpu_name'                => $cpuName,
            'gpu_name'                => $gpuName,
            'cpu_score'               => $cpuScore,
            'gpu_score'               => $gpuScore,
            'resolution'              => $resolution,
            'workload'                => $workload,
            'bottleneck_percent'      => $bottleneckPercent,
            'utilization_percent'     => $utilizationPercent,
            'tier'                    => $tier,
            'tag'                     => $tag,
            'advice'                  => $advice,
            'estimated_wattage'       => $totalWattage,
            'recommended_psu_wattage' => $recommendedPsu,
            'cooling_advice'          => $coolingAdvice,
            'games_fps'               => $games,
        ];
    }

    /**
     * Calculate recommended standard PSU tier with 25% headroom.
     */
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

    /**
     * Extract rated PSU wattage capacity from a PSU Product.
     */
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
     * Generate thermal cooling advice for a given CPU.
     */
    public function generateCoolingAdvice(?Product $cpu): string
    {
        if (! $cpu) {
            return 'Select a CPU to receive thermal dissipation and cooler sizing recommendations.';
        }

        return $this->generateCoolingAdviceFromTdp((int) $cpu->tdp_wattage, $cpu->name);
    }

    private function generateCoolingAdviceFromTdp(int $cpuTdp, string $cpuName): string
    {
        if ($cpuTdp >= 200) {
            return sprintf(
                '%s reaches a high peak TDP of %dW. A 360mm AIO Liquid Cooler (e.g., DeepCool LT720 360mm) is strongly recommended to prevent thermal throttling under sustained loads.',
                $cpuName,
                $cpuTdp
            );
        }

        if ($cpuTdp >= 120) {
            return sprintf(
                '%s operates at %dW TDP. A Dual-Tower 6-Heatpipe Air Cooler (e.g., Thermalright Peerless Assassin 120 SE) or 240mm/360mm AIO is recommended.',
                $cpuName,
                $cpuTdp
            );
        }

        return sprintf(
            '%s has an efficient %dW TDP profile. A standard single-tower air cooler or dual-tower air cooler will easily maintain quiet, low-temperature operation.',
            $cpuName,
            $cpuTdp
        );
    }

    /**
     * Estimate a normalized 1-100 performance score for a CPU or GPU product or array.
     *
     * @param  Product|array<string, mixed>  $component
     */
    public function estimatePerformanceScore(Product|array $component, string $type = 'CPU'): int
    {
        if (is_array($component) && isset($component['score'])) {
            return (int) $component['score'];
        }

        $name = strtoupper($component instanceof Product ? $component->name : (string) ($component['name'] ?? ''));
        $price = (float) ($component instanceof Product ? $component->selling_price : ($component['selling_price'] ?? $component['price'] ?? 0));
        $tdp = (int) ($component instanceof Product ? $component->tdp_wattage : ($component['tdp_wattage'] ?? $component['tdp'] ?? 100));

        // Known benchmark profiles matching legacy CalculatorController & catalog SKUs
        $knownScores = [
            '14900K'      => 99,
            '7800X3D'     => 99,
            '9800X3D'     => 100,
            '7950X'       => 98,
            '14700K'      => 96,
            '13700K'      => 92,
            '14600K'      => 88,
            '7700X'       => 88,
            '7600X'       => 84,
            '13400'       => 75,
            '12400'       => 72,
            '5600'        => 70,
            '4090'        => 100,
            '4080 SUPER'  => 98,
            '4080'        => 96,
            '4070 TI'     => 91,
            '4070 SUPER'  => 84,
            '4070'        => 80,
            '7800 XT'     => 82,
            '4060 TI'     => 68,
            '4060'        => 62,
        ];

        foreach ($knownScores as $keyword => $score) {
            if (str_contains($name, $keyword)) {
                return $score;
            }
        }

        // Heuristic fallback from TDP & price
        if (strtoupper($type) === 'CPU') {
            return max(55, min(99, (int) round(55 + ($tdp * 0.12) + ($price * 0.04))));
        }

        return max(50, min(100, (int) round(50 + ($tdp * 0.14) + ($price * 0.03))));
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

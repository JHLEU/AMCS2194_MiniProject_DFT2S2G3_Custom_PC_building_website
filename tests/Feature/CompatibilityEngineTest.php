<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use App\Services\CompatibilityService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompatibilityEngineTest extends TestCase
{
    use RefreshDatabase;

    protected CompatibilityService $service;
    protected User $admin;
    protected User $warehouseStaff;
    protected User $techStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->service = app(CompatibilityService::class);
        $this->admin = User::where('email', 'admin@custompc.local')->firstOrFail();
        $this->warehouseStaff = User::where('email', 'warehouse@custompc.local')->firstOrFail();
        $this->techStaff = User::where('email', 'tech@custompc.local')->firstOrFail();
        $this->customer = User::where('email', 'customer@custompc.local')->firstOrFail();
    }

    public function test_compatible_am5_ddr5_build_passes_with_zero_errors_and_healthy_psu_headroom(): void
    {
        $cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail(); // AM5, DDR5, 120W
        $mb = Product::where('name', 'ASUS ROG STRIX B650-A GAMING WIFI')->firstOrFail(); // AM5, DDR5, 50W
        $ram = Product::where('name', 'Corsair Vengeance RGB 32GB (2x16GB)')->firstOrFail(); // DDR5, 20W
        $gpu = Product::where('name', 'NVIDIA GeForce RTX 4070 Super 12GB')->firstOrFail(); // 220W
        $ssd = Product::where('name', 'Samsung 990 PRO 2TB NVMe SSD')->firstOrFail(); // 10W
        $psu = Product::where('name', 'Corsair RM850x 850W Fully Modular')->firstOrFail(); // 850W Rated

        $result = $this->service->evaluateBuild([$cpu, $mb, $ram, $gpu, $ssd, $psu]);

        $this->assertTrue($result['compatible']);
        $this->assertSame('compatible', $result['status']);
        $this->assertEmpty($result['errors']);

        // Component TDP: 120 + 50 + 20 + 220 + 10 = 420W (PSU 850W excluded from consumed power)
        // Total estimated wattage: 420W + 60W base = 480W
        $this->assertSame(420, $result['power']['component_tdp_wattage']);
        $this->assertSame(60, $result['power']['base_overhead_wattage']);
        $this->assertSame(480, $result['power']['estimated_total_wattage']);
        $this->assertSame(650, $result['power']['recommended_psu_wattage']); // 480 * 1.25 = 600 -> 650W tier
        $this->assertSame(850, $result['power']['selected_psu_wattage']);
        $this->assertSame(370, $result['power']['psu_headroom_watts']);
        $this->assertSame('ok', $result['power']['psu_status']);
        $this->assertTrue($result['pricing']['all_in_stock']);
    }

    public function test_socket_mismatch_between_lga1700_cpu_and_am5_motherboard_is_flagged(): void
    {
        $intelCpu = Product::where('name', 'Intel Core i7-14700K')->firstOrFail(); // LGA1700
        $am5Mb = Product::where('name', 'ASUS ROG STRIX B650-A GAMING WIFI')->firstOrFail(); // AM5

        $result = $this->service->evaluateBuild([$intelCpu, $am5Mb]);

        $this->assertFalse($result['compatible']);
        $this->assertSame('incompatible', $result['status']);

        $errorCodes = array_column($result['errors'], 'code');
        $this->assertContains('socket_mismatch', $errorCodes);
    }

    public function test_ram_type_mismatch_between_ddr5_motherboard_and_ddr4_ram_is_flagged(): void
    {
        $am5Cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail(); // AM5, DDR5
        $am5Mb = Product::where('name', 'ASUS ROG STRIX B650-A GAMING WIFI')->firstOrFail(); // AM5, DDR5
        $ddr4Ram = Product::where('name', 'Kingston Fury Beast 16GB (2x8GB) DDR4')->firstOrFail(); // DDR4

        $result = $this->service->evaluateBuild([$am5Cpu, $am5Mb, $ddr4Ram]);

        $this->assertFalse($result['compatible']);
        $errorCodes = array_column($result['errors'], 'code');
        $this->assertContains('ram_mismatch', $errorCodes);
    }

    public function test_psu_insufficient_and_tight_headroom_checks(): void
    {
        $intelCpu = Product::where('name', 'Intel Core i7-14700K')->firstOrFail(); // 253W
        $z790Mb = Product::where('name', 'ASUS ROG STRIX Z790-A GAMING WIFI')->firstOrFail(); // 55W
        $ddr5Ram = Product::where('name', 'Corsair Vengeance RGB 32GB (2x16GB)')->firstOrFail(); // 20W
        $gpu = Product::where('name', 'NVIDIA GeForce RTX 4070 Super 12GB')->firstOrFail(); // 220W
        $cooler = Product::where('name', 'DeepCool LT720 360mm AIO Liquid Cooler')->firstOrFail(); // 30W
        $case = Product::where('name', 'NZXT H6 Flow RGB Dual-Chamber Case')->firstOrFail(); // 15W
        $psu550 = Product::where('name', 'Corsair CV550 550W 80+ Bronze PSU')->firstOrFail(); // 550W

        // Component TDP: 253 + 55 + 20 + 220 + 30 + 15 = 593W
        // Plus 60W base = 653W total load -> exceeds 550W PSU
        $insufficientResult = $this->service->evaluateBuild([$intelCpu, $z790Mb, $ddr5Ram, $gpu, $cooler, $case, $psu550]);

        $this->assertFalse($insufficientResult['compatible']);
        $this->assertSame(653, $insufficientResult['power']['estimated_total_wattage']);
        $this->assertSame('insufficient', $insufficientResult['power']['psu_status']);
        $this->assertContains('psu_insufficient', array_column($insufficientResult['errors'], 'code'));

        // Now test tight headroom: 510W load on 550W PSU (between 1.0x and 1.15x)
        // Intel CPU (253W) + GPU (220W) = 473W + 60W base = 533W on 550W PSU (533 * 1.15 = 613W > 550W)
        $tightResult = $this->service->evaluateBuild([$intelCpu, $gpu, $psu550]);
        $this->assertTrue($tightResult['compatible']);
        $this->assertSame('warning', $tightResult['status']);
        $this->assertSame(533, $tightResult['power']['estimated_total_wattage']);
        $this->assertSame('tight', $tightResult['power']['psu_status']);
        $this->assertContains('psu_tight_headroom', array_column($tightResult['warnings'], 'code'));
    }

    public function test_api_evaluate_and_compatible_parts_endpoints(): void
    {
        $am5Cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail();
        $am5Mb = Product::where('name', 'ASUS ROG STRIX B650-A GAMING WIFI')->firstOrFail();
        $lga1700Mb = Product::where('name', 'ASUS ROG STRIX Z790-A GAMING WIFI')->firstOrFail();

        // 1. POST /api/compatibility/evaluate
        $evalResponse = $this->postJson(route('api.compatibility.evaluate'), [
            'product_ids' => [$am5Cpu->id, $am5Mb->id],
        ]);

        $evalResponse->assertOk()
            ->assertJsonPath('compatible', true)
            ->assertJsonPath('power.component_tdp_wattage', 170)
            ->assertJsonPath('power.estimated_total_wattage', 230);

        // 2. GET /api/compatibility/compatible-parts?cpu_id={am5_cpu}&category=Motherboard
        $partsResponse = $this->getJson(route('api.compatibility.compatible-parts', [
            'cpu_id'   => $am5Cpu->id,
            'category' => 'Motherboard',
        ]));

        $partsResponse->assertOk();
        $parts = collect($partsResponse->json('parts'));

        $am5Entry = $parts->firstWhere('id', $am5Mb->id);
        $lga1700Entry = $parts->firstWhere('id', $lga1700Mb->id);

        $this->assertNotNull($am5Entry);
        $this->assertTrue($am5Entry['compatible']);

        $this->assertNotNull($lga1700Entry);
        $this->assertFalse($lga1700Entry['compatible']);
        $this->assertNotEmpty($lga1700Entry['incompatibility_reasons']);
    }

    public function test_api_bottleneck_endpoint_returns_synergy_and_fps_estimates(): void
    {
        $cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail();
        $gpu = Product::where('name', 'NVIDIA GeForce RTX 4070 Super 12GB')->firstOrFail();

        $response = $this->postJson(route('api.compatibility.bottleneck'), [
            'cpu_id'     => $cpu->id,
            'gpu_id'     => $gpu->id,
            'resolution' => '1440',
            'workload'   => 'game_3a',
        ]);

        $response->assertOk()
            ->assertJsonStructure([
                'cpu_name',
                'gpu_name',
                'cpu_score',
                'gpu_score',
                'resolution',
                'workload',
                'bottleneck_percent',
                'utilization_percent',
                'tier',
                'tag',
                'advice',
                'estimated_wattage',
                'recommended_psu_wattage',
                'cooling_advice',
                'games_fps',
            ]);

        $this->assertCount(6, $response->json('games_fps'));
    }

    public function test_erp_compatibility_test_bench_is_accessible_to_all_staff_and_blocked_for_customers(): void
    {
        $url = route('erp.compatibility.index');

        $this->actingAs($this->customer)->get($url)->assertStatus(403);
        $this->actingAs($this->admin)->get($url)->assertStatus(200)->assertSee('Compatibility & Power Test Bench', false);
        $this->actingAs($this->warehouseStaff)->get($url)->assertStatus(200);
        $this->actingAs($this->techStaff)->get($url)->assertStatus(200);
    }
}

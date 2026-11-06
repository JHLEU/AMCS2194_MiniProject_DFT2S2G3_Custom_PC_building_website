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

    public function test_compatible_am5_ddr5_build_passes_with_zero_errors(): void
    {
        $cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail(); // AM5, DDR5
        $mb = Product::where('name', 'ASUS ROG STRIX B650-A GAMING WIFI')->firstOrFail(); // AM5, DDR5
        $ram = Product::where('name', 'Corsair Vengeance RGB 32GB (2x16GB)')->firstOrFail(); // DDR5
        $gpu = Product::where('name', 'NVIDIA GeForce RTX 4070 Super 12GB')->firstOrFail();
        $ssd = Product::where('name', 'Samsung 990 PRO 2TB NVMe SSD')->firstOrFail();

        $result = $this->service->evaluateBuild([$cpu, $mb, $ram, $gpu, $ssd]);

        $this->assertTrue($result['compatible']);
        $this->assertTrue($result['is_compatible']);
        $this->assertSame('compatible', $result['status']);
        $this->assertEmpty($result['errors']);
        $this->assertEmpty($result['warnings']);
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

    public function test_ram_type_mismatch_between_ddr4_motherboard_and_ddr5_ram_is_flagged(): void
    {
        $ddr4Mb = Product::where('name', 'MSI B550M PRO-VDH WIFI')->firstOrFail(); // AM4, DDR4
        $ddr5Ram = Product::where('name', 'Corsair Vengeance RGB 32GB (2x16GB)')->firstOrFail(); // DDR5

        $result = $this->service->evaluateBuild([$ddr4Mb, $ddr5Ram]);

        $this->assertFalse($result['compatible']);
        $errorCodes = array_column($result['errors'], 'code');
        $this->assertContains('ram_mismatch', $errorCodes);
    }

    public function test_compatibility_engine_only_checks_socket_and_ram_not_psu_or_cooling(): void
    {
        $intelCpu = Product::where('name', 'Intel Core i7-14700K')->firstOrFail(); // LGA1700, DDR5
        $z790Mb = Product::where('name', 'ASUS ROG STRIX Z790-A GAMING WIFI')->firstOrFail(); // LGA1700, DDR5
        $ddr5Ram = Product::where('name', 'Corsair Vengeance RGB 32GB (2x16GB)')->firstOrFail(); // DDR5
        $gpu = Product::where('name', 'NVIDIA GeForce RTX 4070 Super 12GB')->firstOrFail();
        $psu550 = Product::where('name', 'Corsair CV550 550W 80+ Bronze PSU')->firstOrFail();

        $result = $this->service->evaluateBuild([$intelCpu, $z790Mb, $ddr5Ram, $gpu, $psu550]);

        $this->assertTrue($result['compatible']);
        $this->assertSame('compatible', $result['status']);
        $this->assertEmpty($result['errors']);
        $this->assertEmpty($result['warnings']);
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
            ->assertJsonPath('performance.cpu_processing_power', $am5Cpu->performance_score)
            ->assertJsonPath('performance.parameters.cpu_processing_power.tier', 'excellent')
            ->assertJsonPath('performance.parameters.cpu_processing_power.color', '#06b6d4');

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

    public function test_api_performance_calculator_endpoint_returns_four_component_scores(): void
    {
        $cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail();
        $gpu = Product::where('name', 'NVIDIA GeForce RTX 4070 Super 12GB')->firstOrFail();
        $ram = Product::where('name', 'Corsair Vengeance RGB 32GB (2x16GB)')->firstOrFail();
        $storage = Product::where('name', 'Samsung 990 PRO 2TB NVMe SSD')->firstOrFail();

        $response = $this->postJson(route('api.compatibility.bottleneck'), [
            'cpu_id'     => $cpu->id,
            'gpu_id'     => $gpu->id,
            'ram_id'     => $ram->id,
            'storage_id' => $storage->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('cpu_processing_power', $cpu->performance_score)
            ->assertJsonPath('gpu_performance', $gpu->performance_score)
            ->assertJsonPath('memory', $ram->performance_score)
            ->assertJsonPath('storage', $storage->performance_score)
            ->assertJsonStructure([
                'overall_score',
                'verdict',
                'advice',
                'cpu_processing_power',
                'gpu_performance',
                'memory',
                'storage',
                'parameters' => [
                    'cpu_processing_power',
                    'gpu_performance',
                    'memory',
                    'storage',
                ],
            ]);
    }

    public function test_erp_compatibility_test_bench_is_accessible_to_all_staff_and_blocked_for_customers(): void
    {
        $url = route('erp.compatibility.index');

        $this->actingAs($this->customer)->get($url)->assertStatus(403);
        $this->actingAs($this->admin)->get($url)->assertStatus(200)->assertSee('Compatibility & Performance Test Bench', false);
        $this->actingAs($this->warehouseStaff)->get($url)->assertStatus(200);
        $this->actingAs($this->techStaff)->get($url)->assertStatus(200);
    }

    public function test_cpu_and_motherboard_ram_type_mismatch_without_ram_selected_is_flagged(): void
    {
        $intelDdr5Cpu = Product::where('name', 'Intel Core i7-14700K')->firstOrFail(); // LGA1700, DDR5
        $lga1700Ddr4Mb = Product::create([
            'name'              => 'MSI PRO B760M-P DDR4',
            'category'          => 'Motherboard',
            'brand'             => 'MSI',
            'socket_type'       => 'LGA1700',
            'ram_type'          => 'DDR4',
            'tdp_wattage'       => 45,
            'performance_score' => 0,
            'cost_price'        => 90.00,
            'selling_price'     => 129.00,
            'warranty_months'   => 36,
        ]);

        $result = $this->service->evaluateBuild([$intelDdr5Cpu, $lga1700Ddr4Mb]);

        $this->assertFalse($result['compatible']);
        $this->assertContains('ram_mismatch', array_column($result['errors'], 'code'));

        $annotated = $this->service->getAnnotatedCatalog(['cpu_id' => $intelDdr5Cpu->id]);
        $mbEntry = $annotated->firstWhere('id', $lga1700Ddr4Mb->id);
        $this->assertNotNull($mbEntry);
        $this->assertFalse($mbEntry['compatible']);
    }

    public function test_performance_calculator_handles_deselected_slots_and_all_four_rating_tiers(): void
    {
        $cpu = Product::where('name', 'AMD Ryzen 7 7800X3D')->firstOrFail();

        // Explicit null IDs (e.g., deselected slots in ERP test bench) should evaluate as 0 / 'none', not fallback to first DB item
        $response = $this->postJson(route('api.compatibility.bottleneck'), [
            'cpu_id'     => $cpu->id,
            'gpu_id'     => null,
            'ram_id'     => null,
            'storage_id' => null,
        ]);

        $response->assertOk()
            ->assertJsonPath('cpu_processing_power', $cpu->performance_score)
            ->assertJsonPath('gpu_performance', 0)
            ->assertJsonPath('memory', 0)
            ->assertJsonPath('storage', 0)
            ->assertJsonPath('parameters.gpu_performance.tier', 'none');

        // Verify all 4 performance tier boundaries: Poor (<50), Average (50-69), Good (70-84), Excellent (85+)
        $this->assertSame('none', $this->service->getPerformanceTier(0)['tier']);
        $this->assertSame('poor', $this->service->getPerformanceTier(40)['tier']);
        $this->assertSame('#ef4444', $this->service->getPerformanceTier(40)['color']);
        $this->assertSame('average', $this->service->getPerformanceTier(60)['tier']);
        $this->assertSame('#f59e0b', $this->service->getPerformanceTier(60)['color']);
        $this->assertSame('good', $this->service->getPerformanceTier(75)['tier']);
        $this->assertSame('#10b981', $this->service->getPerformanceTier(75)['color']);
        $this->assertSame('excellent', $this->service->getPerformanceTier(90)['tier']);
        $this->assertSame('#06b6d4', $this->service->getPerformanceTier(90)['color']);
    }
}

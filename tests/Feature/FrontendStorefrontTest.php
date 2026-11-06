<?php

namespace Tests\Feature;

use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendStorefrontTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    public function test_homepage_renders_with_featured_builds_and_reviews(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $response->assertSee('Engineered For Performance.', false);
        $response->assertSee('Esports Champion Build');
        $response->assertSee('Verified Customer Experiences');
        $response->assertSee('Marcus Tan');
    }

    public function test_pc_builder_loads_hardware_slots_and_catalog(): void
    {
        $response = $this->get(route('builder.index'));

        $response->assertOk();
        $response->assertSee('Interactive PC Builder');
        $response->assertSee('Processor (CPU)');
        $response->assertSee('Motherboard');
        $response->assertSee('Memory (RAM)');
        $response->assertSee('Graphics Card (GPU)');
        $response->assertSee('Power Supply Unit (PSU)');
        $response->assertSee('Build Summary');
        $response->assertSee('CPU Processing Power');
        $response->assertSee('GPU Performance');
        $response->assertSee('Memory');
        $response->assertSee('Storage');
        $response->assertSee('Poor');
        $response->assertSee('Average');
        $response->assertSee('Good');
        $response->assertSee('Excellent');
    }

    public function test_pc_builder_preselects_from_query_parameters(): void
    {
        $cpu = Product::where('category', 'CPU')->firstOrFail();
        $gpu = Product::where('category', 'GPU')->firstOrFail();

        $response = $this->get(route('builder.index', ['cpu_id' => $cpu->id, 'gpu_id' => $gpu->id]));

        $response->assertOk();
        $response->assertSee($cpu->name);
        $response->assertSee($gpu->name);
    }

    public function test_pc_builder_submission_redirects_to_checkout(): void
    {
        $cpu = Product::where('category', 'CPU')->firstOrFail();
        $gpu = Product::where('category', 'GPU')->firstOrFail();

        $response = $this->post(route('builder.checkout'), [
            'product_ids' => [$cpu->id, $gpu->id],
        ]);

        $response->assertRedirect(route('checkout.index', ['product_ids' => [$cpu->id, $gpu->id]]));

        // Follow redirect to checkout page
        $checkoutResponse = $this->get(route('checkout.index', ['product_ids' => [$cpu->id, $gpu->id]]));
        $checkoutResponse->assertOk();
        $checkoutResponse->assertSee($cpu->name);
        $checkoutResponse->assertSee($gpu->name);
    }

    public function test_pc_builder_empty_submission_redirects_back_with_error(): void
    {
        $response = $this->post(route('builder.checkout'), [
            'product_ids' => [],
        ]);

        $response->assertRedirect(route('builder.index'));
        $response->assertSessionHas('error');
    }

    public function test_performance_calculator_renders_and_calculates_four_parameters_via_api(): void
    {
        // 1. Calculator view
        $viewResponse = $this->get(route('calculator.index'));
        $viewResponse->assertOk();
        $viewResponse->assertSee('PC Performance Calculator');
        $viewResponse->assertSee('CPU Processing Power');
        $viewResponse->assertSee('GPU Performance');
        $viewResponse->assertSee('Memory');
        $viewResponse->assertSee('Storage');

        // 2. Evaluation API endpoint
        $cpu = Product::where('category', 'CPU')->firstOrFail();
        $gpu = Product::where('category', 'GPU')->firstOrFail();
        $ram = Product::where('category', 'RAM')->firstOrFail();
        $storage = Product::where('category', 'Storage')->firstOrFail();

        $apiResponse = $this->postJson(route('calculator.calculate'), [
            'cpu_id'     => $cpu->id,
            'gpu_id'     => $gpu->id,
            'ram_id'     => $ram->id,
            'storage_id' => $storage->id,
        ]);

        $apiResponse->assertOk()
            ->assertJsonPath('cpu_processing_power', $cpu->performance_score)
            ->assertJsonPath('gpu_performance', $gpu->performance_score)
            ->assertJsonPath('memory', $ram->performance_score)
            ->assertJsonPath('storage', $storage->performance_score)
            ->assertJsonStructure([
                'overall_score',
                'synergy_score',
                'verdict',
                'cpu_processing_power',
                'gpu_performance',
                'memory',
                'storage',
                'parameters',
            ]);
    }

    public function test_contact_page_renders_store_info_and_whatsapp_link(): void
    {
        $response = $this->get(route('contact'));

        $response->assertOk();
        $response->assertSee('Plaza Low Yat');
        $response->assertSee('+60 3-8888 1234');
        $response->assertSee('+60 16-888 2345');
        $response->assertSee('WhatsApp Instant Chat');
    }
}

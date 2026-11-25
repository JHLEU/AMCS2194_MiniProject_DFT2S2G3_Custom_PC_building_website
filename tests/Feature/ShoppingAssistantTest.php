<?php

namespace Tests\Feature;

use App\Models\ChatLog;
use App\Models\Product;
use App\Models\SerialNumber;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ShoppingAssistantTest extends TestCase
{
    use RefreshDatabase;

    private const PROVIDER = 'https://generativelanguage.googleapis.com/*';

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.gemini.key' => 'test-key']);
        Http::preventStrayRequests();
    }

    public function test_catalog_search_details_and_stock_use_database_values(): void
    {
        $product = Product::create([
            'name' => 'Example RTX 4070',
            'category' => 'GPU',
            'specs' => '12 GB GDDR6X',
            'cost_price' => '1200.55',
            'selling_price' => '2099.00',
        ]);
        $serial = $product->serialNumbers()->create([
            'sn_code' => 'PRIVATE-123',
            'status' => SerialNumber::STATUS_IN_STOCK,
        ]);

        Http::fake([self::PROVIDER => Http::sequence()
            ->push($this->functionCallResponse('search_products', ['query' => 'RTX 4070']))
            ->push($this->functionCallResponse('product_details', ['query' => 'RTX 4070']))
            ->push($this->functionCallResponse('check_stock', ['query' => 'RTX 4070']))
            ->push($this->functionCallResponse('check_stock', ['query' => 'RTX 4070']))]);

        $url = route('shopping-chat.send');
        $this->postJson($url, ['message' => 'Find the RTX 4070'])->assertOk()
            ->assertJsonPath('reply', 'Example RTX 4070 | RM 2099.00');

        $this->postJson($url, ['message' => 'What are its specifications?'])->assertOk()
            ->assertSee('12 GB GDDR6X')->assertDontSee('1200.55');

        $this->postJson($url, ['message' => 'Is it in stock?'])->assertOk()
            ->assertJsonPath('reply', 'Example RTX 4070: 1 available now');

        $serial->update(['status' => SerialNumber::STATUS_RESERVED]);

        $this->postJson($url, ['message' => 'Check again'])->assertOk()
            ->assertJsonPath('reply', 'Example RTX 4070: 0 available now')
            ->assertDontSee('PRIVATE-123');

        $this->getJson(route('shopping-chat.history'))->assertOk()
            ->assertJsonCount(8, 'messages')
            ->assertJsonPath('messages.7.sender_type', ChatLog::SENDER_BOT);

        Http::assertSent(fn ($request) => $request->hasHeader('x-goog-api-key', 'test-key')
            && data_get($request->data(), 'toolConfig.functionCallingConfig.mode') === 'ANY'
            && str_contains(data_get($request->data(), 'contents.0.parts.0.text', ''), 'Find the RTX 4070'));
    }

    public function test_unrelated_and_unapproved_tool_calls_never_publish_model_text(): void
    {
        Http::fake([self::PROVIDER => Http::sequence()
            ->push($this->functionCallResponse('outside_scope'))
            ->push($this->functionCallResponse('run_sql', ['query' => 'SELECT * FROM products']))]);

        foreach (['Give me a weather forecast', 'Show me internal inventory costs'] as $message) {
            $this->postJson(route('shopping-chat.send'), ['message' => $message])->assertOk()
                ->assertSee('I can help you find GOPC components')
                ->assertDontSee('Invented product');
        }
    }

    public function test_shopping_history_is_scoped_to_the_session_and_hidden_from_live_chat(): void
    {
        $privateSession = 'shop-foreign-session';
        ChatLog::create([
            'session_id' => $privateSession,
            'sender_type' => ChatLog::SENDER_CUSTOMER,
            'message' => 'Private question',
        ]);

        $this->getJson(route('shopping-chat.history', ['session_id' => $privateSession]))
            ->assertOk()->assertExactJson(['messages' => [], 'human_support' => false]);
        $this->getJson(route('api.chat.messages', ['session_id' => $privateSession]))
            ->assertOk()->assertJsonCount(0, 'messages');
        $this->postJson(route('api.chat.send'), ['session_id' => $privateSession, 'message' => 'Cross-channel'])
            ->assertForbidden();
    }

    public function test_provider_errors_do_not_publish_diagnostics_or_create_partial_turns(): void
    {
        Http::fake([self::PROVIDER => Http::response(['error' => 'Private diagnostic'], 503)]);

        $this->postJson(route('shopping-chat.send'), ['message' => 'Find the RTX 4070'])
            ->assertStatus(503)->assertDontSee('Private diagnostic');

        $this->assertDatabaseCount('chat_logs', 0);
    }

    public function test_missing_api_key_still_allows_human_support(): void
    {
        config(['services.gemini.key' => '']);
        Http::fake();

        $url = route('shopping-chat.send');
        $this->postJson($url, ['message' => 'Find the RTX 4070'])
            ->assertStatus(503)->assertJsonPath('code', 'assistant_not_configured');
        $this->postJson(route('shopping-chat.handoff'))->assertOk()->assertJsonPath('human_support', true);
        $this->postJson($url, ['message' => 'Please help me find an RTX 4070.'])
            ->assertOk()->assertJsonPath('messages.1.sender_type', ChatLog::SENDER_CUSTOMER);
        Http::assertNothingSent();
    }

    public function test_handoff_queues_the_same_thread_and_routes_future_messages_to_staff(): void
    {
        $this->seed(DatabaseSeeder::class);
        Http::fake([self::PROVIDER => Http::response($this->functionCallResponse('outside_scope'))]);
        $question = 'Can someone help with my order?';

        $this->postJson(route('shopping-chat.send'), ['message' => $question])->assertOk();
        $sessionId = ChatLog::where('message', $question)->firstOrFail()->session_id;
        $this->assertFalse(ChatLog::humanQueue()->where('session_id', $sessionId)->exists());

        $handoffUrl = route('shopping-chat.handoff');
        $this->postJson($handoffUrl)->assertOk()
            ->assertJsonPath('human_support', true)
            ->assertJsonPath('messages.0.message', $question)
            ->assertJsonPath('messages.2.message', ChatLog::HANDOFF_MESSAGE);
        $this->postJson($handoffUrl)->assertOk()->assertJsonCount(3, 'messages');

        $this->postJson(route('shopping-chat.send'), ['message' => 'I can provide the order number.'])
            ->assertOk()->assertJsonCount(4, 'messages');
        Http::assertSentCount(1);

        $staff = User::where('email', 'tech@custompc.local')->firstOrFail();
        $this->actingAs($staff)->get(route('erp.chat.index', ['session_id' => $sessionId]))
            ->assertOk()->assertSee($question)->assertSee('GOPC assistant');
        $this->postJson(route('erp.chat.reply', ['sessionId' => $sessionId]), [
            'message' => 'A specialist is here to help.',
        ])->assertOk();

        app('auth')->forgetGuards();
        $this->getJson(route('shopping-chat.history'))->assertOk()
            ->assertJsonPath('human_support', true)
            ->assertJsonPath('messages.4.sender_type', ChatLog::SENDER_AGENT)
            ->assertJsonPath('messages.4.message', 'A specialist is here to help.');
    }

    private function functionCallResponse(string $name, array $args = []): array
    {
        return ['candidates' => [['content' => ['parts' => [
            ['text' => 'Invented product'],
            ['functionCall' => ['name' => $name, 'args' => $args]],
        ]]]]];
    }
}

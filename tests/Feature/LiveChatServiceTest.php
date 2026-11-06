<?php

namespace Tests\Feature;

use App\Models\ChatLog;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class LiveChatServiceTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $techStaff;
    protected User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@custompc.local')->firstOrFail();
        $this->techStaff = User::where('email', 'tech@custompc.local')->firstOrFail();
        $this->customer = User::where('email', 'customer@custompc.local')->firstOrFail();
    }

    public function test_guest_customer_can_send_message_via_chat_api_and_receives_session_id(): void
    {
        $response = $this->postJson(route('api.chat.send'), [
            'message' => 'Hello, is the RTX 4080 in stock?',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
            ])
            ->assertJsonStructure([
                'session_id',
                'message' => ['id', 'sender_type', 'message'],
                'messages',
            ]);

        $sessionId = $response->json('session_id');
        $this->assertNotEmpty($sessionId);

        $this->assertDatabaseHas('chat_logs', [
            'session_id' => $sessionId,
            'sender_type' => ChatLog::SENDER_CUSTOMER,
            'message' => 'Hello, is the RTX 4080 in stock?',
            'customer_id' => null,
        ]);
    }

    public function test_authenticated_customer_message_attaches_customer_id(): void
    {
        $sessionId = 'cust_sess_custom_123';

        $response = $this->actingAs($this->customer)->postJson(route('api.chat.send'), [
            'session_id' => $sessionId,
            'message' => 'Can you check my RMA ticket status?',
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'status' => 'success',
                'session_id' => $sessionId,
            ]);

        $this->assertDatabaseHas('chat_logs', [
            'session_id' => $sessionId,
            'customer_id' => $this->customer->id,
            'sender_type' => ChatLog::SENDER_CUSTOMER,
            'message' => 'Can you check my RMA ticket status?',
        ]);
    }

    public function test_customer_can_retrieve_conversation_history_via_api(): void
    {
        $sessionId = 'thread_test_456';

        ChatLog::create([
            'session_id' => $sessionId,
            'customer_id' => $this->customer->id,
            'sender_type' => ChatLog::SENDER_CUSTOMER,
            'message' => 'First message from customer',
        ]);

        ChatLog::create([
            'session_id' => $sessionId,
            'customer_id' => $this->customer->id,
            'sender_type' => ChatLog::SENDER_AGENT,
            'message' => 'Reply from technician',
        ]);

        $response = $this->getJson(route('api.chat.messages', ['session_id' => $sessionId]));

        $response->assertStatus(200)
            ->assertJson([
                'session_id' => $sessionId,
            ]);

        $messages = $response->json('messages');
        $this->assertCount(2, $messages);
        $this->assertEquals('First message from customer', $messages[0]['message']);
        $this->assertTrue($messages[0]['is_customer']);
        $this->assertEquals('Reply from technician', $messages[1]['message']);
        $this->assertFalse($messages[1]['is_customer']);
    }

    public function test_customer_message_cannot_be_empty(): void
    {
        $response = $this->postJson(route('api.chat.send'), [
            'message' => '',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['message']);
    }

    public function test_staff_can_view_chat_workbench_with_active_inquiries(): void
    {
        $sessionId = 'active_inquiry_789';

        ChatLog::create([
            'session_id' => $sessionId,
            'customer_id' => $this->customer->id,
            'sender_type' => ChatLog::SENDER_CUSTOMER,
            'message' => 'Need help with custom water cooling setup',
        ]);

        $response = $this->actingAs($this->techStaff)->get(route('erp.chat.index', ['session_id' => $sessionId]));

        $response->assertStatus(200)
            ->assertSee('Customer Service Live Chat Desk')
            ->assertSee('Need help with custom water cooling setup')
            ->assertSee($this->customer->name);
    }

    public function test_staff_can_send_agent_reply_to_customer_thread(): void
    {
        $sessionId = 'reply_thread_101';

        ChatLog::create([
            'session_id' => $sessionId,
            'customer_id' => $this->customer->id,
            'sender_type' => ChatLog::SENDER_CUSTOMER,
            'message' => 'Will my DDR5 6000MHz RAM boot without XMP enabled?',
        ]);

        $response = $this->actingAs($this->techStaff)->post(route('erp.chat.reply', ['sessionId' => $sessionId]), [
            'message' => 'Yes, it will default to JEDEC base speed of 4800MHz until you enable EXPO/XMP in BIOS.',
        ]);

        $response->assertRedirect(route('erp.chat.index', ['session_id' => $sessionId]));

        $this->assertDatabaseHas('chat_logs', [
            'session_id' => $sessionId,
            'sender_type' => ChatLog::SENDER_AGENT,
            'customer_id' => $this->customer->id,
            'message' => 'Yes, it will default to JEDEC base speed of 4800MHz until you enable EXPO/XMP in BIOS.',
        ]);
    }

    public function test_customer_subsequent_poll_receives_the_agent_reply(): void
    {
        $sessionId = 'poll_thread_202';

        // Customer sends inquiry
        $this->postJson(route('api.chat.send'), [
            'session_id' => $sessionId,
            'message' => 'What thermal paste do you apply?',
        ])->assertStatus(201);

        // Tech replies via ERP
        $this->actingAs($this->techStaff)->postJson(route('erp.chat.reply', ['sessionId' => $sessionId]), [
            'message' => 'We apply Arctic MX-6 or Noctua NT-H2 on all custom builds.',
        ])->assertStatus(200);

        // Customer polls messages
        Auth::logout();
        $pollResponse = $this->getJson(route('api.chat.messages', ['session_id' => $sessionId]));

        $pollResponse->assertStatus(200);
        $messages = $pollResponse->json('messages');
        $this->assertCount(2, $messages);
        $this->assertEquals('We apply Arctic MX-6 or Noctua NT-H2 on all custom builds.', $messages[1]['message']);
        $this->assertEquals(ChatLog::SENDER_AGENT, $messages[1]['sender_type']);
    }

    public function test_guests_and_customers_cannot_access_erp_chat_workbench(): void
    {
        Auth::logout();

        // Guest access
        $this->get(route('erp.chat.index'))
            ->assertRedirect(route('login'));

        // Customer access
        $this->actingAs($this->customer)
            ->get(route('erp.chat.index'))
            ->assertStatus(403);

        $this->actingAs($this->customer)
            ->post(route('erp.chat.reply', ['sessionId' => 'any-session']), [
                'message' => 'Unauthorized attempt',
            ])
            ->assertStatus(403);
    }
}

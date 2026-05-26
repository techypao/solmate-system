<?php

namespace Tests\Feature;

use App\Models\ChatConversation;
use App\Models\User;
use App\Notifications\AdminChatEscalationRequestedNotification;
use App\Services\ChatbotReplyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ChatConversationApiTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $role, string $email): User
    {
        return User::query()->create([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);
    }

    public function test_customer_message_uses_bot_reply_by_default(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"answer":"You can request an inspection from the customer dashboard.","suggestions":["How do I track an inspection?","What happens after inspection?"]}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_default@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'How do I request an inspection?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('messages.0.sender_type', 'user')
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'You can request an inspection from the customer dashboard.');
    }

    public function test_customer_can_trigger_admin_escalation_with_human_keywords(): void
    {
        Notification::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_escalation@example.com');
        $admin = $this->createUser(User::ROLE_ADMIN, 'chat_admin_escalation@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'I need a human admin to help me.',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_ADMIN)
            ->assertJsonPath('conversation.is_awaiting_admin', true)
            ->assertJsonPath('messages.1.sender_type', 'system');

        Notification::assertSentTo($admin, AdminChatEscalationRequestedNotification::class);
        Http::assertNothingSent();
    }

    public function test_repeated_fallback_responses_escalate_the_conversation(): void
    {
        Notification::fake();
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"answer":"'.ChatbotReplyService::SCOPE_FALLBACK.'","suggestions":["How do quotations work?","How do I request an inspection?"]}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_fallback@example.com');
        $admin = $this->createUser(User::ROLE_ADMIN, 'chat_admin_fallback@example.com');
        Sanctum::actingAs($customer);

        $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Tell me a joke.',
        ])->assertOk()->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Tell me another joke.',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_ADMIN)
            ->assertJsonPath('conversation.is_awaiting_admin', true);

        Notification::assertSentTo($admin, AdminChatEscalationRequestedNotification::class);
    }

    public function test_admin_can_take_over_and_reply_in_same_thread(): void
    {
        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_admin_reply@example.com');
        $admin = $this->createUser(User::ROLE_ADMIN, 'chat_admin_reply@example.com');
        $conversation = ChatConversation::query()->create([
            'customer_user_id' => $customer->id,
            'status' => ChatConversation::STATUS_ADMIN,
            'takeover_requested_at' => now(),
            'escalated_at' => now(),
            'last_message_at' => now(),
        ]);
        $conversation->messages()->create([
            'sender_user_id' => $customer->id,
            'sender_type' => 'user',
            'body' => 'Can someone help me?',
        ]);

        Sanctum::actingAs($admin);

        $response = $this->postJson('/api/admin/chat/conversations/'.$conversation->id.'/messages', [
            'message' => 'Hi, this is Admin Support. I can help now.',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_ADMIN)
            ->assertJsonPath('conversation.admin.id', $admin->id);

        $messages = $response->json('messages');

        $this->assertSame('user', $messages[0]['sender_type']);
        $this->assertSame('system', $messages[1]['sender_type']);
        $this->assertSame('admin', $messages[2]['sender_type']);
        $this->assertSame('Hi, this is Admin Support. I can help now.', $messages[2]['body']);
    }

    public function test_bot_stays_inactive_after_admin_takeover(): void
    {
        config()->set('services.gemini.api_key', 'test-key');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_after_takeover@example.com');
        $conversation = ChatConversation::query()->create([
            'customer_user_id' => $customer->id,
            'status' => ChatConversation::STATUS_ADMIN,
            'takeover_requested_at' => now(),
            'escalated_at' => now(),
            'admin_joined_at' => now(),
            'last_message_at' => now(),
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Thanks, I have another question.',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_ADMIN);

        $this->assertCount(1, $response->json('messages'));
        Http::assertNothingSent();
        $this->assertSame($conversation->id, $response->json('conversation.id'));
    }

    public function test_admin_can_return_conversation_to_chatbot_and_bot_replies_again(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"answer":"SolBot is back and can help with inspections.","suggestions":["What happens after inspection?"]}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_return_to_bot@example.com');
        $admin = $this->createUser(User::ROLE_ADMIN, 'chat_admin_return_to_bot@example.com');
        $conversation = ChatConversation::query()->create([
            'customer_user_id' => $customer->id,
            'admin_user_id' => $admin->id,
            'status' => ChatConversation::STATUS_ADMIN,
            'takeover_requested_at' => now(),
            'escalated_at' => now(),
            'admin_joined_at' => now(),
            'last_message_at' => now(),
        ]);

        Sanctum::actingAs($admin);

        $releaseResponse = $this->postJson('/api/admin/chat/conversations/'.$conversation->id.'/return-to-bot');

        $releaseResponse->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.admin', null)
            ->assertJsonPath('conversation.is_admin_active', false)
            ->assertJsonPath('messages.0.sender_type', 'system');

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Can the chatbot help me again?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT);

        $messages = $response->json('messages');

        $this->assertSame('system', $messages[0]['sender_type']);
        $this->assertSame('user', $messages[1]['sender_type']);
        $this->assertSame('bot', $messages[2]['sender_type']);
        $this->assertSame('SolBot is back and can help with inspections.', $messages[2]['body']);
    }
}
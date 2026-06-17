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
        $user = User::query()->create([
            'name' => ucfirst($role).' User',
            'email' => $email,
            'password' => 'password123',
            'role' => $role,
        ]);

        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
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
            'message' => 'How do I track an inspection?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('messages.0.sender_type', 'user')
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'You can request an inspection from the customer dashboard.');
    }

    public function test_gemini_receives_short_recent_context_for_follow_up_questions(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"answer":"Solar benefits usually mean lower bills and better long-term value, depending on usage and site conditions.","suggestions":["What affects solar savings?","Why does inspection matter?"]}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_memory@example.com');
        $conversation = ChatConversation::query()->create([
            'customer_user_id' => $customer->id,
            'status' => ChatConversation::STATUS_BOT,
            'last_message_at' => now(),
        ]);
        $conversation->messages()->create([
            'sender_user_id' => $customer->id,
            'sender_type' => 'user',
            'body' => 'What is ROI?',
        ]);
        $conversation->messages()->create([
            'sender_type' => 'bot',
            'body' => 'ROI means the value you get back from solar through bill savings compared with the system cost.',
        ]);

        Sanctum::actingAs($customer);

        $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Can you explain that solar benefit in simpler words?',
        ])->assertOk()
            ->assertJsonPath('messages.3.body', 'Solar benefits usually mean lower bills and better long-term value, depending on usage and site conditions.');

        Http::assertSent(function ($request) {
            $text = $request->data()['contents'][0]['parts'][0]['text'] ?? '';

            return str_contains($text, 'Recent chat context for follow-up understanding only:')
                && str_contains($text, 'Customer: What is ROI?')
                && str_contains($text, 'SolBot: ROI means the value you get back from solar through bill savings compared with the system cost.');
        });
    }

    public function test_known_quotation_question_uses_exact_faq_mapping_before_gemini(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"answer":"Your pre-inspection estimate is only an early guide.","suggestions":["What is a pre-inspection estimate?","Who prepares the final quotation?"]}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_exact_quotation@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => '  HOW DO I REQUEST A QUOTATION?  ',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'You can request a quotation by creating a pre-inspection estimate in the customer app. After inspection, the technician prepares the inspection-based quotation with the final technical details.')
            ->assertJsonPath('messages.1.metadata.suggestions.0', 'What is a pre-inspection estimate?');

        Http::assertNothingSent();
    }

    public function test_reworded_quotation_question_uses_local_topic_matching_before_gemini(): void
    {
        config()->set('services.gemini.api_key', 'test-key');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_fuzzy_quotation@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'How can I get a quote for solar?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'You can request a quotation by creating a pre-inspection estimate in the customer app. After inspection, the technician prepares the inspection-based quotation with the final technical details.');

        Http::assertNothingSent();
    }

    public function test_simple_questions_use_local_fallback_when_backend_gemini_key_is_missing(): void
    {
        config()->set('services.gemini.api_key', '');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_offline_roi@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'What is ROI?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('conversation.bot_fallback_count', 0)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'ROI means the value you get back from solar through bill savings compared with the system cost. In simple terms, it shows whether the long-term savings can make the installation worth it.');

        Http::assertNothingSent();
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

    public function test_mentions_of_admin_do_not_trigger_takeover_without_explicit_request(): void
    {
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'candidates' => [[
                    'content' => [
                        'parts' => [[
                            'text' => '{"answer":"Awaiting admin approval means an admin still needs to review the request before it moves forward.","suggestions":["What happens after admin approval?","How long does admin review usually take?"]}',
                        ]],
                    ],
                ]],
            ]),
        ]);

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_admin_question@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'What does awaiting admin approval mean?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'Awaiting admin approval means an admin still needs to review the request before it moves forward.');
    }

    public function test_account_specific_status_questions_are_redirected_without_live_data_claims(): void
    {
        config()->set('services.gemini.api_key', 'test-key');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_account_specific@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Can you check my quotation status?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'I cannot view live account records or check your actual request, quotation, or notification status. Please open the related SolMate screen for updates, or ask an admin if you need help with a specific record.')
            ->assertJsonPath('messages.1.metadata.admin_handoff_reason', 'account_specific')
            ->assertJsonPath('messages.1.metadata.admin_handoff_label', 'Account-specific help');

        Http::assertNothingSent();
    }

    public function test_admin_handoff_reason_is_preserved_when_customer_accepts_account_specific_help(): void
    {
        Notification::fake();
        config()->set('services.gemini.api_key', 'test-key');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_account_reason@example.com');
        $admin = $this->createUser(User::ROLE_ADMIN, 'chat_admin_account_reason@example.com');
        Sanctum::actingAs($customer);

        $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Can you check my quotation status?',
        ])->assertOk();

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Talk to a real admin',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_ADMIN)
            ->assertJsonPath('conversation.is_awaiting_admin', true)
            ->assertJsonPath('messages.3.sender_type', 'system')
            ->assertJsonPath('messages.3.metadata.reason', 'account_specific')
            ->assertJsonPath('messages.3.metadata.reason_label', 'Account-specific help');

        Sanctum::actingAs($admin);

        $this->getJson('/api/admin/chat/conversations')
            ->assertOk()
            ->assertJsonPath('conversations.0.escalation_reason', 'account_specific')
            ->assertJsonPath('conversations.0.escalation_reason_label', 'Account-specific help');

        Notification::assertSentTo($admin, AdminChatEscalationRequestedNotification::class);
        Http::assertNothingSent();
    }

    public function test_technical_sizing_questions_require_inspection_without_gemini(): void
    {
        config()->set('services.gemini.api_key', 'test-key');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_technical_safety@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'How many panels do I need and what size system should I install?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', 'For exact system sizing, wiring, safety, or installation decisions, a technician needs to inspect the site first. I can explain the general SolMate process, but final technical guidance should come from the inspection and technician assessment.');

        Http::assertNothingSent();
    }

    public function test_non_energy_solar_system_questions_are_out_of_scope_without_gemini(): void
    {
        config()->set('services.gemini.api_key', 'test-key');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_planets_out_of_scope@example.com');
        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Tell me about solar system planets.',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', "I'm not sure about that, but I can connect you to an admin if you'd like.");

        Http::assertNothingSent();
    }

    public function test_scope_fallback_reply_offers_admin_without_escalating(): void
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

        $firstResponse = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Tell me a joke.',
        ]);

        $firstResponse->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('conversation.bot_fallback_count', 0)
            ->assertJsonPath('messages.1.sender_type', 'bot')
            ->assertJsonPath('messages.1.body', "I'm not sure about that, but I can connect you to an admin if you'd like.");

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Tell me another joke.',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('conversation.bot_fallback_count', 0);

        Notification::assertNothingSent();
    }

    public function test_user_can_confirm_admin_offer_after_fallback_message(): void
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

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_confirm_offer@example.com');
        $admin = $this->createUser(User::ROLE_ADMIN, 'chat_admin_confirm_offer@example.com');
        Sanctum::actingAs($customer);

        $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Tell me a joke.',
        ])->assertOk()->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'Yes please.',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_ADMIN)
            ->assertJsonPath('conversation.is_awaiting_admin', true);

        Notification::assertSentTo($admin, AdminChatEscalationRequestedNotification::class);
    }

    public function test_upstream_errors_use_local_simple_answers_without_escalating(): void
    {
        Notification::fake();
        config()->set('services.gemini.api_key', 'test-key');

        Http::fake([
            'https://generativelanguage.googleapis.com/*' => Http::response([
                'error' => [
                    'message' => 'Temporary upstream failure.',
                ],
            ], 500),
        ]);

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_bot_errors@example.com');
        $this->createUser(User::ROLE_ADMIN, 'chat_admin_bot_errors@example.com');
        Sanctum::actingAs($customer);

        $this->postJson('/api/chat/conversation/messages', [
            'message' => 'How do I request an inspection?',
        ])->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.bot_fallback_count', 0)
            ->assertJsonPath('messages.1.body', 'You can request an inspection from the customer dashboard when you need a site check or technical assessment before final recommendations.');

        $this->postJson('/api/chat/conversation/messages', [
            'message' => 'How do I request an inspection?',
        ])->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.bot_fallback_count', 0);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'How do I request an inspection?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('conversation.bot_fallback_count', 0);

        Notification::assertNothingSent();
    }

    public function test_auto_escalated_conversation_can_resume_bot_replies_for_simple_questions(): void
    {
        config()->set('services.gemini.api_key', '');
        Http::fake();

        $customer = $this->createUser(User::ROLE_CUSTOMER, 'chat_customer_resume_bot@example.com');
        $conversation = ChatConversation::query()->create([
            'customer_user_id' => $customer->id,
            'status' => ChatConversation::STATUS_ADMIN,
            'bot_fallback_count' => 3,
            'takeover_requested_at' => now(),
            'escalated_at' => now(),
            'last_message_at' => now(),
        ]);

        $conversation->messages()->create([
            'sender_user_id' => $customer->id,
            'sender_type' => 'user',
            'body' => 'What is ROI?',
        ]);

        $conversation->messages()->create([
            'sender_type' => 'bot',
            'body' => "I'm having trouble responding right now. You can try again, or I can connect you to an admin if you'd like.",
            'metadata' => [
                'status' => 'fallback',
                'event' => 'admin_offer',
                'reason' => 'bot_error',
                'retry_count' => 3,
            ],
        ]);

        $conversation->messages()->create([
            'sender_user_id' => $customer->id,
            'sender_type' => 'system',
            'body' => 'This conversation has been escalated. A real admin will continue the chat shortly.',
            'metadata' => [
                'event' => 'escalated',
                'reason' => 'repeated_bot_failures',
            ],
        ]);

        Sanctum::actingAs($customer);

        $response = $this->postJson('/api/chat/conversation/messages', [
            'message' => 'What is ROI?',
        ]);

        $response->assertOk()
            ->assertJsonPath('conversation.status', ChatConversation::STATUS_BOT)
            ->assertJsonPath('conversation.is_awaiting_admin', false)
            ->assertJsonPath('conversation.bot_fallback_count', 0);

        $messages = $response->json('messages');

        $this->assertSame('bot', $messages[array_key_last($messages)]['sender_type']);
        $this->assertSame('ROI means the value you get back from solar through bill savings compared with the system cost. In simple terms, it shows whether the long-term savings can make the installation worth it.', $messages[array_key_last($messages)]['body']);
        Http::assertNothingSent();
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

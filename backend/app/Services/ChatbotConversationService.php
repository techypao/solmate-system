<?php

namespace App\Services;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Notifications\AdminChatEscalationRequestedNotification;
use Illuminate\Support\Facades\DB;
use Throwable;

class ChatbotConversationService
{
    private const ADMIN_REQUEST_PATTERNS = [
        '/\b(admin|agent|human|representative|staff)\b/i',
        '/\b(real person|real admin|live agent|live support|customer service|human support)\b/i',
        '/\b(need help|need support|can someone help|talk to someone|talk to support)\b/i',
    ];

    private const FALLBACK_ESCALATION_THRESHOLD = 2;

    public function __construct(
        private readonly ChatbotReplyService $replyService,
    ) {
    }

    public function getOrCreateForCustomer(User $customer): ChatConversation
    {
        return ChatConversation::query()->firstOrCreate(
            ['customer_user_id' => $customer->id],
            [
                'status' => ChatConversation::STATUS_BOT,
                'last_message_at' => now(),
            ],
        );
    }

    public function sendCustomerMessage(User $customer, string $body): ChatConversation
    {
        return DB::transaction(function () use ($customer, $body): ChatConversation {
            $conversation = $this->getOrCreateForCustomer($customer);

            $this->appendMessage($conversation, ChatMessage::SENDER_USER, $body, $customer);

            if ($conversation->isUnderAdminControl()) {
                return $this->loadConversation($conversation);
            }

            if ($this->userRequestedAdmin($body)) {
                $this->escalateToAdmin($conversation, 'user_requested_human', $customer);

                return $this->loadConversation($conversation);
            }

            try {
                $reply = $this->replyService->send($body);
            } catch (Throwable $throwable) {
                $this->appendMessage(
                    $conversation,
                    ChatMessage::SENDER_BOT,
                    'I ran into a problem while responding. A real admin can continue this conversation if needed.',
                    null,
                    ['status' => 'error']
                );
                $this->escalateToAdmin($conversation, 'bot_error', $customer);

                return $this->loadConversation($conversation);
            }

            $this->appendMessage(
                $conversation,
                ChatMessage::SENDER_BOT,
                $reply['text'],
                null,
                [
                    'suggestions' => $reply['suggestions'],
                    'status' => ($reply['is_fallback'] ?? false) ? 'fallback' : 'default',
                ],
            );

            if ($reply['is_fallback'] ?? false) {
                $conversation->increment('bot_fallback_count');
            } else {
                $conversation->forceFill(['bot_fallback_count' => 0])->save();
            }

            $conversation->refresh();

            if ($conversation->bot_fallback_count >= self::FALLBACK_ESCALATION_THRESHOLD) {
                $this->escalateToAdmin($conversation, 'repeated_fallback', $customer);
            }

            return $this->loadConversation($conversation);
        });
    }

    public function requestAdminTakeover(ChatConversation $conversation, User $customer, string $reason = 'manual_escalation'): ChatConversation
    {
        return DB::transaction(function () use ($conversation, $customer, $reason): ChatConversation {
            $this->escalateToAdmin($conversation, $reason, $customer);

            return $this->loadConversation($conversation);
        });
    }

    public function takeOverConversation(ChatConversation $conversation, User $admin): ChatConversation
    {
        return DB::transaction(function () use ($conversation, $admin): ChatConversation {
            $conversation->forceFill([
                'status' => ChatConversation::STATUS_ADMIN,
                'admin_user_id' => $admin->id,
                'takeover_requested_at' => $conversation->takeover_requested_at ?? now(),
                'escalated_at' => $conversation->escalated_at ?? now(),
                'admin_joined_at' => $conversation->admin_joined_at ?? now(),
                'last_message_at' => now(),
            ])->save();

            $latestSystemMessage = $conversation->messages()
                ->where('sender_type', ChatMessage::SENDER_SYSTEM)
                ->latest('id')
                ->first();

            if (! $latestSystemMessage || ! str_contains($latestSystemMessage->body, 'real admin')) {
                $this->appendMessage(
                    $conversation,
                    ChatMessage::SENDER_SYSTEM,
                    $admin->name.' joined the chat. You are now chatting with a real admin.',
                    $admin,
                    ['event' => 'admin_joined'],
                );
            }

            return $this->loadConversation($conversation);
        });
    }

    public function sendAdminMessage(ChatConversation $conversation, User $admin, string $body): ChatConversation
    {
        return DB::transaction(function () use ($conversation, $admin, $body): ChatConversation {
            $conversation = $this->takeOverConversation($conversation, $admin);

            $this->appendMessage($conversation, ChatMessage::SENDER_ADMIN, $body, $admin);

            return $this->loadConversation($conversation);
        });
    }

    public function releaseConversationToBot(ChatConversation $conversation, User $admin): ChatConversation
    {
        return DB::transaction(function () use ($conversation, $admin): ChatConversation {
            $conversation->forceFill([
                'status' => ChatConversation::STATUS_BOT,
                'admin_user_id' => null,
                'admin_joined_at' => null,
                'bot_fallback_count' => 0,
                'last_message_at' => now(),
            ])->save();

            $this->appendMessage(
                $conversation,
                ChatMessage::SENDER_SYSTEM,
                $admin->name.' returned this conversation to SolBot. You can continue chatting with the chatbot now.',
                $admin,
                ['event' => 'returned_to_bot'],
            );

            return $this->loadConversation($conversation);
        });
    }

    private function escalateToAdmin(ChatConversation $conversation, string $reason, User $triggeredBy): void
    {
        $wasBotControlled = ! $conversation->isUnderAdminControl();

        $conversation->forceFill([
            'status' => ChatConversation::STATUS_ADMIN,
            'takeover_requested_at' => $conversation->takeover_requested_at ?? now(),
            'escalated_at' => $conversation->escalated_at ?? now(),
            'last_message_at' => now(),
        ])->save();

        if ($wasBotControlled) {
            $this->appendMessage(
                $conversation,
                ChatMessage::SENDER_SYSTEM,
                'This conversation has been escalated. A real admin will continue the chat shortly.',
                $triggeredBy,
                ['event' => 'escalated', 'reason' => $reason],
            );

            User::query()
                ->where('role', User::ROLE_ADMIN)
                ->get()
                ->each(fn (User $admin) => $admin->notify(new AdminChatEscalationRequestedNotification(
                    $conversation->fresh('customer'),
                    str_replace('_', ' ', $reason),
                    $triggeredBy->id,
                )));
        }
    }

    private function appendMessage(
        ChatConversation $conversation,
        string $senderType,
        string $body,
        ?User $sender = null,
        array $metadata = [],
    ): ChatMessage {
        $message = $conversation->messages()->create([
            'sender_user_id' => $sender?->id,
            'sender_type' => $senderType,
            'body' => trim($body),
            'metadata' => $metadata === [] ? null : $metadata,
        ]);

        $conversation->forceFill([
            'last_message_at' => $message->created_at,
        ])->save();

        return $message;
    }

    private function userRequestedAdmin(string $body): bool
    {
        foreach (self::ADMIN_REQUEST_PATTERNS as $pattern) {
            if (preg_match($pattern, $body) === 1) {
                return true;
            }
        }

        return false;
    }

    private function loadConversation(ChatConversation $conversation): ChatConversation
    {
        return $conversation->fresh(['customer', 'admin', 'messages.sender']) ?? $conversation->load(['customer', 'admin', 'messages.sender']);
    }
}
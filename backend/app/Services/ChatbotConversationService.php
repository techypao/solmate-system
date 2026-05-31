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
    private const AUTO_RESUME_ESCALATION_REASONS = [
        'bot_error',
        'repeated_bot_failures',
        'repeated_fallback',
    ];

    private const ADMIN_REQUEST_PATTERNS = [
        '/\b(?:talk|speak|chat)\s+(?:to|with)\s+(?:a\s+)?(?:real\s+)?(?:person|human|admin|agent|representative|staff|support)\b/i',
        '/\b(?:connect|transfer|route|escalat(?:e|ion)|put me through)\s+(?:me\s+)?(?:to|with)?\s*(?:a\s+)?(?:real\s+)?(?:person|human|admin|agent|representative|staff|support)\b/i',
        '/\b(?:need|want|prefer)\s+(?:a\s+)?(?:real\s+)?(?:person|human|agent|representative|staff|support)\b/i',
        '/\b(?:real person|real admin|live agent|live support|customer service|human support)\b/i',
    ];

    private const ADMIN_OFFER_CONFIRMATION_PATTERNS = [
        '/^\s*(?:yes|yes please|please|sure|okay|ok|go ahead|do that|that works)\s*[!.?]*\s*$/i',
        '/\b(?:connect|transfer|route|escalat(?:e|ion)|put me through)\s+(?:me\s+)?(?:to|with)?\s*(?:a\s+)?(?:real\s+)?(?:person|human|admin|agent|representative|staff|support)\b/i',
    ];

    private const BOT_FAILURE_ESCALATION_THRESHOLD = 3;

    private const BOT_FALLBACK_MESSAGE = "I'm not sure about that, but I can connect you to an admin if you'd like.";

    private const BOT_ERROR_MESSAGE = "I'm having trouble responding right now. You can try again, or I can connect you to an admin if you'd like.";

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
                if ($this->shouldAutoResumeBot($conversation)) {
                    $this->resumeBotControl($conversation);
                    $conversation->refresh();
                } else {
                    return $this->loadConversation($conversation);
                }
            }

            if ($conversation->isUnderAdminControl()) {
                return $this->loadConversation($conversation);
            }

            if ($this->userRequestedAdmin($body) || $this->userConfirmedAdminOffer($conversation, $body)) {
                $this->escalateToAdmin($conversation, 'user_requested_human', $customer);

                return $this->loadConversation($conversation);
            }

            try {
                $reply = $this->replyService->send($body);
            } catch (Throwable $throwable) {
                $this->handleBotFailure($conversation, $customer, 'bot_error');

                return $this->loadConversation($conversation);
            }

            if (! $this->hasUsableReply($reply)) {
                $this->handleBotFailure($conversation, $customer, 'empty_response');

                return $this->loadConversation($conversation);
            }

            $replyText = trim((string) $reply['text']);
            $isFallbackReply = (bool) ($reply['is_fallback'] ?? false);

            $this->appendMessage(
                $conversation,
                ChatMessage::SENDER_BOT,
                $isFallbackReply ? self::BOT_FALLBACK_MESSAGE : $replyText,
                null,
                [
                    'suggestions' => $reply['suggestions'] ?? [],
                    'status' => $isFallbackReply ? 'fallback' : 'default',
                    'event' => $isFallbackReply ? 'admin_offer' : null,
                ],
            );

            $this->resetBotFailureCount($conversation);

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

    /**
     * @param  array<string, mixed>  $reply
     */
    private function hasUsableReply(array $reply): bool
    {
        return trim((string) ($reply['text'] ?? '')) !== '';
    }

    private function handleBotFailure(ChatConversation $conversation, User $customer, string $reason): void
    {
        $conversation->increment('bot_fallback_count');
        $conversation->refresh();

        $this->appendMessage(
            $conversation,
            ChatMessage::SENDER_BOT,
            self::BOT_ERROR_MESSAGE,
            null,
            [
                'status' => 'fallback',
                'event' => 'admin_offer',
                'reason' => $reason,
                'retry_count' => $conversation->bot_fallback_count,
            ],
        );

        if ($conversation->bot_fallback_count >= self::BOT_FAILURE_ESCALATION_THRESHOLD) {
            $this->escalateToAdmin($conversation, 'repeated_bot_failures', $customer);
        }
    }

    private function resetBotFailureCount(ChatConversation $conversation): void
    {
        if ($conversation->bot_fallback_count === 0) {
            return;
        }

        $conversation->forceFill(['bot_fallback_count' => 0])->save();
    }

    private function shouldAutoResumeBot(ChatConversation $conversation): bool
    {
        if (! $conversation->isAwaitingAdmin()) {
            return false;
        }

        $latestSystemMessage = $conversation->messages()
            ->where('sender_type', ChatMessage::SENDER_SYSTEM)
            ->latest('id')
            ->first();

        $reason = $latestSystemMessage?->metadata['reason'] ?? null;

        return is_string($reason) && in_array($reason, self::AUTO_RESUME_ESCALATION_REASONS, true);
    }

    private function resumeBotControl(ChatConversation $conversation): void
    {
        $conversation->forceFill([
            'status' => ChatConversation::STATUS_BOT,
            'admin_user_id' => null,
            'admin_joined_at' => null,
        ])->save();
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

    private function userConfirmedAdminOffer(ChatConversation $conversation, string $body): bool
    {
        $latestBotMessage = $conversation->messages()
            ->where('sender_type', ChatMessage::SENDER_BOT)
            ->latest('id')
            ->first();

        if (($latestBotMessage?->metadata['event'] ?? null) !== 'admin_offer') {
            return false;
        }

        foreach (self::ADMIN_OFFER_CONFIRMATION_PATTERNS as $pattern) {
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
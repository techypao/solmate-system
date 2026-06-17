<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatbotConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatConversationController extends Controller
{
    public function __construct(
        private readonly ChatbotConversationService $conversationService,
    ) {}

    public function show(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $conversation = $this->conversationService->getOrCreateForCustomer($customer)->load(['customer', 'admin', 'messages.sender']);

        return response()->json($this->serializeConversation($conversation));
    }

    public function storeMessage(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $conversation = $this->conversationService->sendCustomerMessage($customer, $validated['message']);

        return response()->json($this->serializeConversation($conversation));
    }

    public function escalate(Request $request): JsonResponse
    {
        /** @var User $customer */
        $customer = $request->user();
        $validated = $request->validate([
            'reason' => ['sometimes', 'string', 'max:80'],
        ]);
        $conversation = $this->conversationService->getOrCreateForCustomer($customer);
        $conversation = $this->conversationService->requestAdminTakeover(
            $conversation,
            $customer,
            $validated['reason'] ?? 'manual_escalation',
        );

        return response()->json($this->serializeConversation($conversation));
    }

    /**
     * @return array<string, mixed>
     */
    private function serializeConversation(ChatConversation $conversation): array
    {
        return [
            'conversation' => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'is_admin_active' => $conversation->isUnderAdminControl(),
                'is_awaiting_admin' => $conversation->isAwaitingAdmin(),
                'bot_fallback_count' => $conversation->bot_fallback_count,
                'customer' => $conversation->customer ? [
                    'id' => $conversation->customer->id,
                    'name' => $conversation->customer->name,
                ] : null,
                'admin' => $conversation->admin ? [
                    'id' => $conversation->admin->id,
                    'name' => $conversation->admin->name,
                ] : null,
                'escalated_at' => $conversation->escalated_at?->toIso8601String(),
                'admin_joined_at' => $conversation->admin_joined_at?->toIso8601String(),
                'last_message_at' => $conversation->last_message_at?->toIso8601String(),
            ],
            'messages' => $conversation->messages->map(fn (ChatMessage $message) => [
                'id' => $message->id,
                'sender_type' => $message->sender_type,
                'sender_name' => $message->sender?->name,
                'body' => $message->body,
                'metadata' => $message->metadata ?? [],
                'created_at' => $message->created_at?->toIso8601String(),
            ])->values(),
        ];
    }
}

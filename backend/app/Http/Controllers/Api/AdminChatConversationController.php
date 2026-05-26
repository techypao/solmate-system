<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Services\ChatbotConversationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminChatConversationController extends Controller
{
    public function __construct(
        private readonly ChatbotConversationService $conversationService,
    ) {
    }

    public function index(): JsonResponse
    {
        $conversations = ChatConversation::query()
            ->with(['customer', 'admin', 'messages' => fn ($query) => $query->latest('id')->limit(1)])
            ->orderByDesc('last_message_at')
            ->orderByDesc('id')
            ->get();

        return response()->json([
            'conversations' => $conversations->map(fn (ChatConversation $conversation) => [
                'id' => $conversation->id,
                'status' => $conversation->status,
                'is_awaiting_admin' => $conversation->isAwaitingAdmin(),
                'customer' => [
                    'id' => $conversation->customer?->id,
                    'name' => $conversation->customer?->name,
                    'email' => $conversation->customer?->email,
                ],
                'admin' => $conversation->admin ? [
                    'id' => $conversation->admin->id,
                    'name' => $conversation->admin->name,
                ] : null,
                'latest_message' => $conversation->messages->first()?->body,
                'latest_sender_type' => $conversation->messages->first()?->sender_type,
                'last_message_at' => $conversation->last_message_at?->toIso8601String(),
            ])->values(),
        ]);
    }

    public function show(ChatConversation $conversation): JsonResponse
    {
        return response()->json($this->serializeConversation($conversation->load(['customer', 'admin', 'messages.sender'])));
    }

    public function takeOver(Request $request, ChatConversation $conversation): JsonResponse
    {
        $conversation = $this->conversationService->takeOverConversation($conversation, $request->user());

        return response()->json($this->serializeConversation($conversation));
    }

    public function returnToBot(Request $request, ChatConversation $conversation): JsonResponse
    {
        $conversation = $this->conversationService->releaseConversationToBot($conversation, $request->user());

        return response()->json($this->serializeConversation($conversation));
    }

    public function storeMessage(Request $request, ChatConversation $conversation): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:4000'],
        ]);

        $conversation = $this->conversationService->sendAdminMessage($conversation, $request->user(), $validated['message']);

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
                'customer' => $conversation->customer ? [
                    'id' => $conversation->customer->id,
                    'name' => $conversation->customer->name,
                    'email' => $conversation->customer->email,
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
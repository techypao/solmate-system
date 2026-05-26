<?php

namespace App\Notifications;

use App\Models\ChatConversation;

class AdminChatEscalationRequestedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly ChatConversation $conversation,
        private readonly string $reason,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $customerName = $this->conversation->customer?->name ?? 'Customer';

        return $this->buildPayload([
            'type' => 'admin_chat_escalation_requested',
            'title' => 'Chat requires admin takeover',
            'message' => "{$customerName} needs a real admin in chat. Reason: {$this->reason}.",
            'entity_type' => 'chat_conversation',
            'entity_id' => $this->conversation->id,
            'target_screen' => 'AdminChat',
            'target_params' => [
                'conversationId' => $this->conversation->id,
            ],
            'status' => 'pending',
        ]);
    }
}
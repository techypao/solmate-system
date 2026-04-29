<?php

namespace App\Notifications;

use App\Models\Testimony;

class AdminNewTestimonyNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly Testimony $testimony,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $customerName = $this->testimony->user?->name ?? 'A customer';

        return $this->buildPayload([
            'type' => 'testimony_created',
            'title' => 'New Customer Testimony',
            'message' => $this->message($customerName),
            'entity_type' => 'testimony',
            'entity_id' => $this->testimony->id,
            'target_screen' => 'AdminTestimonyManagement',
            'target_params' => [
                'testimonyId' => $this->testimony->id,
            ],
            'status' => $this->testimony->status,
        ]);
    }

    private function message(string $customerName): string
    {
        if ($this->testimony->service_request_id) {
            return "{$customerName} submitted a new testimony for request #{$this->testimony->service_request_id}.";
        }

        if ($this->testimony->inspection_request_id) {
            return "{$customerName} submitted a new testimony for request #{$this->testimony->inspection_request_id}.";
        }

        return "{$customerName} submitted a new testimony.";
    }
}

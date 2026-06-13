<?php

namespace App\Notifications;

use App\Models\User;

class AdminCustomerDeleteRequestedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly User $customer,
        private readonly string $reason,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildPayload([
            'type' => 'admin_customer_delete_requested',
            'title' => 'Customer Deletion Requested',
            'message' => "{$this->customer->name} requested to delete their account.",
            'entity_type' => 'customer',
            'entity_id' => $this->customer->id,
            'target_screen' => 'AdminCustomers',
            'target_params' => [
                'customerId' => $this->customer->id,
            ],
            'status' => 'pending',
            'delete_request_reason' => $this->reason,
        ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\Quotation;

class AdminQuotationDiscountRequestedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly Quotation $quotation,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $this->quotation->loadMissing('customer');
        $customerName = $this->quotation->customer?->name ?: 'A customer';

        return $this->buildPayload([
            'type' => 'quotation_discount_requested',
            'title' => 'Discount Request Received',
            'message' => "{$customerName} requested a discount for inspection-based quotation #{$this->quotation->id}.",
            'entity_type' => 'quotation',
            'entity_id' => $this->quotation->id,
            'target_screen' => 'QuotationItemBuilder',
            'target_params' => [
                'quotationId' => $this->quotation->id,
                'inspectionRequestId' => $this->quotation->inspection_request_id,
            ],
            'status' => $this->quotation->discount_request_status,
        ]);
    }
}

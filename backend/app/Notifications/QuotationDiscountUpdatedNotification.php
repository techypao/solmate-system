<?php

namespace App\Notifications;

use App\Models\Quotation;

class QuotationDiscountUpdatedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly Quotation $quotation,
        private readonly string $outcome,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $isApplied = $this->outcome === 'applied';
        $amount = number_format((float) ($this->quotation->admin_discount_amount ?? 0), 2);

        return $this->buildPayload([
            'type' => $isApplied ? 'quotation_discount_applied' : 'quotation_discount_rejected',
            'title' => $isApplied ? 'Quotation Discount Applied' : 'Discount Request Reviewed',
            'message' => $isApplied
                ? "An admin discount of PHP {$amount} was applied to your inspection-based quotation."
                : 'Your discount request was reviewed by admin. Please check your inspection-based quotation for details.',
            'entity_type' => 'quotation',
            'entity_id' => $this->quotation->id,
            'target_screen' => 'CustomerFinalQuotationDetails',
            'target_params' => [
                'quotationId' => $this->quotation->id,
                'inspectionRequestId' => $this->quotation->inspection_request_id,
            ],
            'status' => $this->quotation->discount_request_status,
        ]);
    }
}

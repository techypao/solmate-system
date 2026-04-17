<?php

namespace App\Notifications;

use App\Models\Quotation;

class FinalQuotationAvailableNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly Quotation $quotation,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildPayload([
            'type' => 'final_quotation_available',
            'title' => 'Final Quotation Available',
            'message' => 'Your final quotation is now available for review.',
            'entity_type' => 'quotation',
            'entity_id' => $this->quotation->id,
            'target_screen' => 'CustomerFinalQuotationDetails',
            'target_params' => [
                'quotationId' => $this->quotation->id,
                'inspectionRequestId' => $this->quotation->inspection_request_id,
            ],
            'status' => $this->quotation->status,
        ]);
    }
}

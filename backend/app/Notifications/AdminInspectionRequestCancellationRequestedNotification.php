<?php

namespace App\Notifications;

use App\Models\InspectionRequest;

class AdminInspectionRequestCancellationRequestedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly InspectionRequest $inspectionRequest,
        private readonly string $cancellationNote,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildPayload([
            'type' => 'admin_inspection_request_cancellation_requested',
            'title' => 'Cancellation Requested',
            'message' => "A customer has requested cancellation for inspection request #{$this->inspectionRequest->id}. Please review and update the status.",
            'entity_type' => 'inspection_request',
            'entity_id' => $this->inspectionRequest->id,
            'target_screen' => 'AdminInspectionRequestDetails',
            'target_params' => [
                'requestId' => $this->inspectionRequest->id,
            ],
            'status' => $this->inspectionRequest->status,
            'cancellation_note' => $this->cancellationNote,
        ]);
    }
}

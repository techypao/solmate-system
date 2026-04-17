<?php

namespace App\Notifications;

use App\Models\InspectionRequest;
use Illuminate\Support\Str;

class InspectionRequestStatusUpdatedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly InspectionRequest $inspectionRequest,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = Str::headline(str_replace('_', ' ', $this->inspectionRequest->status));

        return $this->buildPayload([
            'type' => 'inspection_request_status_updated',
            'title' => 'Inspection Request Updated',
            'message' => "Your inspection request status has been updated to {$statusLabel}.",
            'entity_type' => 'inspection_request',
            'entity_id' => $this->inspectionRequest->id,
            'target_screen' => 'CustomerInspectionRequestDetails',
            'target_params' => [
                'inspectionRequestId' => $this->inspectionRequest->id,
            ],
            'status' => $this->inspectionRequest->status,
        ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\InspectionRequest;

class InspectionRequestAssignedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly InspectionRequest $inspectionRequest,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildPayload([
            'type' => 'inspection_request_assigned',
            'title' => 'Inspection Request Assigned',
            'message' => "You have been assigned to inspection request #{$this->inspectionRequest->id}.",
            'entity_type' => 'inspection_request',
            'entity_id' => $this->inspectionRequest->id,
            'target_screen' => 'TechnicianInspectionRequestDetails',
            'target_params' => [
                'inspectionRequestId' => $this->inspectionRequest->id,
            ],
            'status' => $this->inspectionRequest->status,
        ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\InspectionRequest;

class AdminNewInspectionRequestNotification extends BaseDatabaseNotification
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
            'type' => 'admin_new_inspection_request',
            'title' => 'New Inspection Request',
            'message' => "A customer submitted inspection request #{$this->inspectionRequest->id}.",
            'entity_type' => 'inspection_request',
            'entity_id' => $this->inspectionRequest->id,
            'target_screen' => 'AdminInspectionRequestDetails',
            'target_params' => [
                'inspectionRequestId' => $this->inspectionRequest->id,
            ],
            'status' => $this->inspectionRequest->status,
        ]);
    }
}

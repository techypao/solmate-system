<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

class ServiceRequestAssignedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildPayload([
            'type' => 'service_request_assigned',
            'title' => 'Service Request Assigned',
            'message' => "You have been assigned to service request #{$this->serviceRequest->id}.",
            'entity_type' => 'service_request',
            'entity_id' => $this->serviceRequest->id,
            'target_screen' => 'TechnicianServiceRequestDetails',
            'target_params' => [
                'requestId' => $this->serviceRequest->id,
            ],
            'status' => $this->serviceRequest->status,
        ]);
    }
}

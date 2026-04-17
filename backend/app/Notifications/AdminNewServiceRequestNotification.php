<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

class AdminNewServiceRequestNotification extends BaseDatabaseNotification
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
            'type' => 'admin_new_service_request',
            'title' => 'New Service Request',
            'message' => "A customer submitted service request #{$this->serviceRequest->id}.",
            'entity_type' => 'service_request',
            'entity_id' => $this->serviceRequest->id,
            'target_screen' => 'AdminServiceRequestDetails',
            'target_params' => [
                'requestId' => $this->serviceRequest->id,
            ],
            'status' => $this->serviceRequest->status,
        ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Support\Str;

class ServiceRequestStatusUpdatedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $statusLabel = Str::headline(str_replace('_', ' ', $this->serviceRequest->status));

        return $this->buildPayload([
            'type' => 'service_request_status_updated',
            'title' => 'Service Request Updated',
            'message' => "Your service request status has been updated to {$statusLabel}.",
            'entity_type' => 'service_request',
            'entity_id' => $this->serviceRequest->id,
            'target_screen' => 'CustomerRequestDetails',
            'target_params' => [
                'requestId' => $this->serviceRequest->id,
            ],
            'status' => $this->serviceRequest->status,
        ]);
    }
}

<?php

namespace App\Notifications;

use App\Models\ServiceRequest;

class AdminServiceRequestCancellationRequestedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        private readonly string $cancellationNote,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        return $this->buildPayload([
            'type' => 'admin_service_request_cancellation_requested',
            'title' => 'Cancellation Requested',
            'message' => "A customer has requested cancellation for service request #{$this->serviceRequest->id}. Please review and update the status.",
            'entity_type' => 'service_request',
            'entity_id' => $this->serviceRequest->id,
            'target_screen' => 'AdminServiceRequestDetails',
            'target_params' => [
                'requestId' => $this->serviceRequest->id,
            ],
            'status' => $this->serviceRequest->status,
            'cancellation_note' => $this->cancellationNote,
        ]);
    }
}

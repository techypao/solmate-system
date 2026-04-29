<?php

namespace App\Notifications;

use App\Models\ServiceRequest;
use Illuminate\Support\Str;

class AdminServiceCompletionRequestedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly ServiceRequest $serviceRequest,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        [$serviceType, $serviceLabel] = $this->serviceTypeMeta();
        $technicianName = $this->serviceRequest->technician?->name ?? 'A technician';
        $customerName = $this->serviceRequest->customer?->name ?? 'the customer';

        return $this->buildPayload([
            'type' => "{$serviceType}_completed",
            'title' => "{$serviceLabel} Marked as Completed",
            'message' => "{$technicianName} marked {$serviceLabel} for {$customerName} as completed.",
            'entity_type' => 'service_request',
            'entity_id' => $this->serviceRequest->id,
            'target_screen' => 'AdminServiceRequestDetails',
            'target_params' => [
                'requestId' => $this->serviceRequest->id,
                'requestType' => $serviceType,
            ],
            'status' => $this->serviceRequest->status,
        ]);
    }

    private function serviceTypeMeta(): array
    {
        $normalizedRequestType = Str::of((string) $this->serviceRequest->request_type)
            ->lower()
            ->replace(['_', '-'], ' ')
            ->value();

        if (Str::contains($normalizedRequestType, 'installation')) {
            return ['installation', 'Installation'];
        }

        return ['maintenance', 'Maintenance'];
    }
}

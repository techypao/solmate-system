<?php

namespace App\Notifications;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use Illuminate\Support\Str;

class AdminCompletionReportSubmittedNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly ServiceRequest|InspectionRequest $requestModel,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $technicianName = $this->requestModel->technician?->name ?? 'A technician';
        $customerName = $this->requestModel->customer?->name ?? 'the customer';

        if ($this->requestModel instanceof InspectionRequest) {
            return $this->buildPayload([
                'type' => 'inspection_completion_report_submitted',
                'title' => 'Inspection Completion Notes Submitted',
                'message' => "{$technicianName} submitted inspection completion notes for {$customerName}.",
                'entity_type' => 'inspection_request',
                'entity_id' => $this->requestModel->id,
                'target_screen' => 'AdminInspectionRequestDetails',
                'target_params' => [
                    'inspectionRequestId' => $this->requestModel->id,
                ],
                'status' => $this->requestModel->status,
            ]);
        }

        $serviceLabel = $this->serviceLabel($this->requestModel);
        $serviceType = Str::slug(Str::lower($serviceLabel), '_');

        return $this->buildPayload([
            'type' => "{$serviceType}_completion_report_submitted",
            'title' => "{$serviceLabel} Completion Notes Submitted",
            'message' => "{$technicianName} submitted {$serviceLabel} completion notes for {$customerName}.",
            'entity_type' => 'service_request',
            'entity_id' => $this->requestModel->id,
            'target_screen' => 'AdminServiceRequestDetails',
            'target_params' => [
                'requestId' => $this->requestModel->id,
                'requestType' => $serviceType,
            ],
            'status' => $this->requestModel->status,
        ]);
    }

    private function serviceLabel(ServiceRequest $serviceRequest): string
    {
        $normalizedRequestType = Str::of((string) $serviceRequest->request_type)
            ->lower()
            ->replace(['_', '-'], ' ')
            ->value();

        if (Str::contains($normalizedRequestType, 'installation')) {
            return 'Installation';
        }

        if (Str::contains($normalizedRequestType, 'repair')) {
            return 'Repair';
        }

        if (Str::contains($normalizedRequestType, 'maintenance')) {
            return 'Maintenance';
        }

        return 'Service';
    }
}

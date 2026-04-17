<?php

namespace App\Services;

use App\Models\InspectionRequest;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\User;
use App\Notifications\AdminNewInspectionRequestNotification;
use App\Notifications\AdminNewServiceRequestNotification;
use App\Notifications\FinalQuotationAvailableNotification;
use App\Notifications\InspectionRequestAssignedNotification;
use App\Notifications\InspectionRequestStatusUpdatedNotification;
use App\Notifications\ScheduleRescheduledNotification;
use App\Notifications\ServiceRequestAssignedNotification;
use App\Notifications\ServiceRequestStatusUpdatedNotification;
use Illuminate\Support\Collection;

class InAppNotificationService
{
    public function notifyAdminsOfNewServiceRequest(ServiceRequest $serviceRequest, User $actor): void
    {
        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(new AdminNewServiceRequestNotification($serviceRequest, $actor->id))
        );
    }

    public function notifyAdminsOfNewInspectionRequest(InspectionRequest $inspectionRequest, User $actor): void
    {
        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(new AdminNewInspectionRequestNotification($inspectionRequest, $actor->id))
        );
    }

    public function notifyTechnicianOfServiceRequestAssignment(ServiceRequest $serviceRequest, ?int $actorId = null): void
    {
        $serviceRequest->loadMissing('technician');

        if (! $serviceRequest->technician) {
            return;
        }

        $serviceRequest->technician->notify(
            new ServiceRequestAssignedNotification($serviceRequest, $actorId)
        );
    }

    public function notifyCustomerOfServiceRequestStatusUpdate(ServiceRequest $serviceRequest, ?int $actorId = null): void
    {
        $serviceRequest->loadMissing('customer');

        if (! $serviceRequest->customer) {
            return;
        }

        $serviceRequest->customer->notify(
            new ServiceRequestStatusUpdatedNotification($serviceRequest, $actorId)
        );
    }

    public function notifyServiceRequestRescheduled(
        ServiceRequest $serviceRequest,
        ?string $oldDate,
        ?int $actorId = null
    ): void {
        $serviceRequest->loadMissing(['customer', 'technician']);

        if ($serviceRequest->customer) {
            $serviceRequest->customer->notify(new ScheduleRescheduledNotification(
                'service_request',
                $serviceRequest->id,
                'customer',
                $oldDate,
                $this->dateOnly($serviceRequest->date_needed),
                $serviceRequest->status,
                $actorId
            ));
        }

        if ($serviceRequest->technician) {
            $serviceRequest->technician->notify(new ScheduleRescheduledNotification(
                'service_request',
                $serviceRequest->id,
                'technician',
                $oldDate,
                $this->dateOnly($serviceRequest->date_needed),
                $serviceRequest->status,
                $actorId
            ));
        }
    }

    public function notifyTechnicianOfInspectionRequestAssignment(InspectionRequest $inspectionRequest, ?int $actorId = null): void
    {
        $inspectionRequest->loadMissing('technician');

        if (! $inspectionRequest->technician) {
            return;
        }

        $inspectionRequest->technician->notify(
            new InspectionRequestAssignedNotification($inspectionRequest, $actorId)
        );
    }

    public function notifyCustomerOfInspectionRequestStatusUpdate(InspectionRequest $inspectionRequest, ?int $actorId = null): void
    {
        $inspectionRequest->loadMissing('customer');

        if (! $inspectionRequest->customer) {
            return;
        }

        $inspectionRequest->customer->notify(
            new InspectionRequestStatusUpdatedNotification($inspectionRequest, $actorId)
        );
    }

    public function notifyInspectionRequestRescheduled(
        InspectionRequest $inspectionRequest,
        ?string $oldDate,
        ?int $actorId = null
    ): void {
        $inspectionRequest->loadMissing(['customer', 'technician']);

        if ($inspectionRequest->customer) {
            $inspectionRequest->customer->notify(new ScheduleRescheduledNotification(
                'inspection_request',
                $inspectionRequest->id,
                'customer',
                $oldDate,
                $this->dateOnly($inspectionRequest->date_needed),
                $inspectionRequest->status,
                $actorId
            ));
        }

        if ($inspectionRequest->technician) {
            $inspectionRequest->technician->notify(new ScheduleRescheduledNotification(
                'inspection_request',
                $inspectionRequest->id,
                'technician',
                $oldDate,
                $this->dateOnly($inspectionRequest->date_needed),
                $inspectionRequest->status,
                $actorId
            ));
        }
    }

    public function notifyCustomerOfFinalQuotationAvailable(Quotation $quotation, ?int $actorId = null): void
    {
        $quotation->loadMissing('customer');

        if (! $quotation->customer) {
            return;
        }

        $quotation->customer->notify(
            new FinalQuotationAvailableNotification($quotation, $actorId)
        );
    }

    private function adminRecipients(): Collection
    {
        return User::query()
            ->where('role', User::ROLE_ADMIN)
            ->get();
    }

    private function dateOnly(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        return (string) $value;
    }
}

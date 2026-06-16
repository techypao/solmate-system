<?php

namespace App\Services;

use App\Models\InspectionRequest;
use App\Models\Promotion;
use App\Models\Quotation;
use App\Models\ServiceRequest;
use App\Models\Testimony;
use App\Models\User;
use App\Notifications\AdminCompletionReportSubmittedNotification;
use App\Notifications\AdminCustomerDeleteRequestedNotification;
use App\Notifications\AdminInspectionRequestCancellationRequestedNotification;
use App\Notifications\AdminNewInspectionRequestNotification;
use App\Notifications\AdminNewServiceRequestNotification;
use App\Notifications\AdminServiceRequestCancellationRequestedNotification;
use App\Notifications\AdminNewTestimonyNotification;
use App\Notifications\AdminQuotationDiscountRequestedNotification;
use App\Notifications\FinalQuotationAvailableNotification;
use App\Notifications\NewPromotionNotification;
use App\Notifications\QuotationDiscountUpdatedNotification;
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

    public function notifyAdminsOfServiceRequestCancellation(
        ServiceRequest $serviceRequest,
        string $cancellationNote,
        ?int $actorId = null
    ): void {
        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(
                new AdminServiceRequestCancellationRequestedNotification($serviceRequest, $cancellationNote, $actorId)
            )
        );
    }

    public function notifyAdminsOfNewInspectionRequest(InspectionRequest $inspectionRequest, User $actor): void
    {
        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(new AdminNewInspectionRequestNotification($inspectionRequest, $actor->id))
        );
    }

    public function notifyAdminsOfInspectionRequestCancellation(
        InspectionRequest $inspectionRequest,
        string $cancellationNote,
        ?int $actorId = null
    ): void {
        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(
                new AdminInspectionRequestCancellationRequestedNotification($inspectionRequest, $cancellationNote, $actorId)
            )
        );
    }

    public function notifyAdminsOfNewTestimony(Testimony $testimony, ?int $actorId = null): void
    {
        $testimony->loadMissing('user');

        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(new AdminNewTestimonyNotification($testimony, $actorId))
        );
    }

    public function notifyAdminsOfCustomerDeleteRequest(User $customer): void
    {
        $reason = (string) $customer->delete_request_reason;

        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(
                new AdminCustomerDeleteRequestedNotification($customer, $reason, $customer->id)
            )
        );
    }

    public function notifyAdminsOfCompletionReportSubmission(ServiceRequest|InspectionRequest $requestModel, ?int $actorId = null): void
    {
        $requestModel->loadMissing(['customer', 'technician']);

        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(new AdminCompletionReportSubmittedNotification($requestModel, $actorId))
        );
    }

    public function notifyAdminsOfServiceCompletionRequest(ServiceRequest $serviceRequest, ?int $actorId = null): void
    {
        $this->notifyAdminsOfCompletionReportSubmission($serviceRequest, $actorId);
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

    public function notifyAdminsOfQuotationDiscountRequest(Quotation $quotation, ?int $actorId = null): void
    {
        $quotation->loadMissing('customer');

        $this->adminRecipients()->each(
            fn (User $admin) => $admin->notify(new AdminQuotationDiscountRequestedNotification($quotation, $actorId))
        );
    }

    public function notifyCustomerOfQuotationDiscountUpdate(
        Quotation $quotation,
        string $outcome,
        ?int $actorId = null
    ): void {
        $quotation->loadMissing('customer');

        if (! $quotation->customer) {
            return;
        }

        $quotation->customer->notify(new QuotationDiscountUpdatedNotification($quotation, $outcome, $actorId));
    }

    public function notifyAllCustomersOfNewPromotion(Promotion $promotion, ?int $actorId = null): void
    {
        User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->get()
            ->each(fn (User $customer) => $customer->notify(
                new NewPromotionNotification($promotion, $actorId)
            ));
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

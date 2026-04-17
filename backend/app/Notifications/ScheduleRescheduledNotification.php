<?php

namespace App\Notifications;

class ScheduleRescheduledNotification extends BaseDatabaseNotification
{
    public function __construct(
        private readonly string $entityType,
        private readonly int $entityId,
        private readonly string $audience,
        private readonly ?string $oldDate,
        private readonly ?string $newDate,
        private readonly ?string $status = null,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function toArray(object $notifiable): array
    {
        $label = $this->entityType === 'inspection_request'
            ? 'Inspection Schedule Updated'
            : 'Service Schedule Updated';

        return $this->buildPayload([
            'type' => 'schedule_rescheduled',
            'title' => $label,
            'message' => $this->message(),
            'entity_type' => $this->entityType,
            'entity_id' => $this->entityId,
            'target_screen' => $this->targetScreen(),
            'target_params' => $this->targetParams(),
            'status' => $this->status,
        ]);
    }

    private function message(): string
    {
        $newDate = $this->formatDate($this->newDate) ?? 'a new schedule';
        $oldDate = $this->formatDate($this->oldDate);

        if ($this->entityType === 'inspection_request') {
            if ($this->audience === 'technician') {
                return $oldDate
                    ? "Assigned inspection request #{$this->entityId} was rescheduled from {$oldDate} to {$newDate}."
                    : "Assigned inspection request #{$this->entityId} was rescheduled to {$newDate}.";
            }

            return $oldDate
                ? "Your inspection date was rescheduled from {$oldDate} to {$newDate}."
                : "Your inspection date was rescheduled to {$newDate}.";
        }

        if ($this->audience === 'technician') {
            return $oldDate
                ? "Assigned service request #{$this->entityId} was rescheduled from {$oldDate} to {$newDate}."
                : "Assigned service request #{$this->entityId} was rescheduled to {$newDate}.";
        }

        return $oldDate
            ? "Your preferred service date was rescheduled from {$oldDate} to {$newDate}."
            : "Your preferred service date was rescheduled to {$newDate}.";
    }

    private function targetScreen(): string
    {
        return match ([$this->audience, $this->entityType]) {
            ['technician', 'inspection_request'] => 'TechnicianInspectionRequestDetails',
            ['technician', 'service_request'] => 'TechnicianServiceRequestDetails',
            ['customer', 'inspection_request'] => 'CustomerInspectionRequestDetails',
            default => 'CustomerRequestDetails',
        };
    }

    private function targetParams(): array
    {
        if ($this->entityType === 'inspection_request') {
            return ['inspectionRequestId' => $this->entityId];
        }

        return ['requestId' => $this->entityId];
    }
}

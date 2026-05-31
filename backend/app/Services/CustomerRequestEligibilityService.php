<?php

namespace App\Services;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class CustomerRequestEligibilityService
{
    private const TERMINAL_STATUSES = ['completed', 'cancelled', 'declined'];

    public function ensureCustomerCanCreateRequest(int $userId): void
    {
        User::query()->lockForUpdate()->findOrFail($userId);

        if ($this->hasOngoingInspectionRequest($userId) || $this->hasOngoingServiceRequest($userId)) {
            throw ValidationException::withMessages([
                'request' => 'You already have an ongoing inspection, installation, or maintenance request. Please wait until it is completed, cancelled, or declined before submitting another request.',
            ]);
        }
    }

    private function hasOngoingInspectionRequest(int $userId): bool
    {
        return InspectionRequest::query()
            ->where('user_id', $userId)
            ->whereNotIn('status', self::TERMINAL_STATUSES)
            ->exists();
    }

    private function hasOngoingServiceRequest(int $userId): bool
    {
        return ServiceRequest::query()
            ->where('user_id', $userId)
            ->whereNotIn('status', self::TERMINAL_STATUSES)
            ->exists();
    }
}
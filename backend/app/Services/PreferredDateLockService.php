<?php

namespace App\Services;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class PreferredDateLockService
{
    public const ACTIVE_LOCK_STATUSES = [
        'pending',
        'approved',
        'scheduled',
        'assigned',
        'in_progress',
    ];

    /** Maximum number of inspection bookings allowed per date. */
    public const INSPECTION_MAX_BOOKINGS = 3;

    public const REQUEST_TYPE_INSPECTION = 'inspection';

    private const DATE_FIELD = 'date_needed';

    private const STATUS_FIELD = 'status';

    private const REQUEST_TYPE_FIELD = 'request_type';

    private const LOCK_TTL_SECONDS = 10;

    private const LOCK_WAIT_SECONDS = 5;

    public function ensureDateIsAvailable(
        DateTimeInterface|string|null $preferredDate,
        ?int $excludeRecordId = null,
        ?string $excludeModelClass = null,
        string $requestType = self::REQUEST_TYPE_INSPECTION
    ): void {
        if (! $this->isDateLocked($preferredDate, $excludeRecordId, $excludeModelClass, $requestType)) {
            return;
        }

        throw $this->reservedDateValidationException();
    }

    /**
     * Determine whether a date is locked for the given request type.
     *
     * Inspection: locked when the date already has 3+ inspection bookings
     *             OR when any service (installation/maintenance) booking exists.
     *
     * Installation / Maintenance: locked when any service booking exists on that
     *             date OR when any inspection booking exists.
     */
    public function isDateLocked(
        DateTimeInterface|string|null $preferredDate,
        ?int $excludeRecordId = null,
        ?string $excludeModelClass = null,
        string $requestType = self::REQUEST_TYPE_INSPECTION
    ): bool {
        if ($preferredDate === null || $preferredDate === '') {
            return false;
        }

        $normalizedDate = $this->normalizeDate($preferredDate);

        if ($requestType === self::REQUEST_TYPE_INSPECTION) {
            // Lock if the inspection slot is full (>= 3 active bookings).
            if ($this->activeInspectionBookingCount($normalizedDate, $excludeRecordId, $excludeModelClass) >= self::INSPECTION_MAX_BOOKINGS) {
                return true;
            }

            return $this->activeNonInspectionServiceQuery($normalizedDate)->exists();
        }

        // Installation / Maintenance: locked if any service booking exists (excluding self)
        $serviceQuery = ServiceRequest::query()
            ->whereDate(self::DATE_FIELD, $normalizedDate)
            ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES);

        if ($excludeRecordId !== null && $excludeModelClass === ServiceRequest::class) {
            $serviceQuery->whereKeyNot($excludeRecordId);
        }

        if ($serviceQuery->exists()) {
            return true;
        }

        if (InspectionRequest::query()
            ->whereDate(self::DATE_FIELD, $normalizedDate)
            ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
            ->exists()) {
            return true;
        }

        return $this->activeManualInspectionServiceQuery($normalizedDate)->exists();
    }

    /**
     * Return unavailable dates for the given request type.
     *
     * Inspection: dates where inspection bookings >= 3, plus dates with any
     *             service (installation/maintenance) booking.
     *
     * Installation / Maintenance: dates with any service booking, plus dates
     *             with any inspection booking.
     */
    public function getUnavailableDates(string $requestType = self::REQUEST_TYPE_INSPECTION): array
    {
        $dates = [];

        if ($requestType === self::REQUEST_TYPE_INSPECTION) {
            // Dates where inspection capacity is reached (>= 3 active bookings)
            $inspectionDates = InspectionRequest::query()
                ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
                ->whereNotNull(self::DATE_FIELD)
                ->pluck(self::DATE_FIELD)
                ->map(fn ($date) => $this->normalizeDate($date))
                ->all();

            $manualInspectionDates = ServiceRequest::query()
                ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
                ->where(self::REQUEST_TYPE_FIELD, ServiceRequest::MANUAL_INSPECTION_REQUEST_TYPE)
                ->whereNotNull(self::DATE_FIELD)
                ->pluck(self::DATE_FIELD)
                ->map(fn ($date) => $this->normalizeDate($date))
                ->all();

            $inspectionBookingCounts = array_count_values(array_merge($inspectionDates, $manualInspectionDates));

            foreach ($inspectionBookingCounts as $date => $bookingCount) {
                if ($bookingCount >= self::INSPECTION_MAX_BOOKINGS) {
                    $dates[] = $date;
                }
            }

            // Dates with any non-inspection service booking block inspection too.
            $serviceDates = $this->activeNonInspectionServiceBaseQuery()
                ->whereNotNull(self::DATE_FIELD)
                ->pluck(self::DATE_FIELD)
                ->all();

            foreach ($serviceDates as $date) {
                $dates[] = $this->normalizeDate($date);
            }
        } else {
            // Installation / Maintenance: any service booking blocks the date
            $serviceDates = ServiceRequest::query()
                ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
                ->whereNotNull(self::DATE_FIELD)
                ->pluck(self::DATE_FIELD)
                ->all();

            foreach ($serviceDates as $date) {
                $dates[] = $this->normalizeDate($date);
            }

            // Any inspection booking also blocks installation/maintenance.
            $inspectionDates = InspectionRequest::query()
                ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
                ->whereNotNull(self::DATE_FIELD)
                ->pluck(self::DATE_FIELD)
                ->all();

            foreach ($inspectionDates as $date) {
                $dates[] = $this->normalizeDate($date);
            }

            $manualInspectionDates = $this->activeManualInspectionServiceBaseQuery()
                ->whereNotNull(self::DATE_FIELD)
                ->pluck(self::DATE_FIELD)
                ->all();

            foreach ($manualInspectionDates as $date) {
                $dates[] = $this->normalizeDate($date);
            }
        }

        $uniqueDates = array_values(array_unique($dates));
        sort($uniqueDates);

        return $uniqueDates;
    }

    public function withLockedDates(
        array $preferredDates,
        Closure $callback,
        ?int $timeoutSeconds = null
    ): mixed {
        $normalizedDates = collect($preferredDates)
            ->filter(fn (DateTimeInterface|string|null $preferredDate) => filled($preferredDate))
            ->map(fn (DateTimeInterface|string $preferredDate) => $this->normalizeDate($preferredDate))
            ->unique()
            ->sort()
            ->values()
            ->all();

        return $this->acquireDateLocks(
            $normalizedDates,
            $callback,
            $timeoutSeconds ?? self::LOCK_WAIT_SECONDS
        );
    }

    private function normalizeDate(DateTimeInterface|string $preferredDate): string
    {
        return Carbon::parse($preferredDate)->toDateString();
    }

    private function activeInspectionBookingCount(
        string $normalizedDate,
        ?int $excludeRecordId = null,
        ?string $excludeModelClass = null
    ): int {
        $inspectionQuery = InspectionRequest::query()
            ->whereDate(self::DATE_FIELD, $normalizedDate)
            ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES);

        if ($excludeRecordId !== null && $excludeModelClass === InspectionRequest::class) {
            $inspectionQuery->whereKeyNot($excludeRecordId);
        }

        $manualInspectionQuery = $this->activeManualInspectionServiceQuery($normalizedDate);

        if ($excludeRecordId !== null && $excludeModelClass === ServiceRequest::class) {
            $manualInspectionQuery->whereKeyNot($excludeRecordId);
        }

        return $inspectionQuery->count() + $manualInspectionQuery->count();
    }

    private function activeNonInspectionServiceQuery(string $normalizedDate)
    {
        return $this->activeNonInspectionServiceBaseQuery()
            ->whereDate(self::DATE_FIELD, $normalizedDate);
    }

    private function activeNonInspectionServiceBaseQuery()
    {
        return ServiceRequest::query()
            ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
            ->where(function ($query) {
                $query
                    ->whereNull(self::REQUEST_TYPE_FIELD)
                    ->orWhere(self::REQUEST_TYPE_FIELD, '!=', ServiceRequest::MANUAL_INSPECTION_REQUEST_TYPE);
            });
    }

    private function activeManualInspectionServiceQuery(string $normalizedDate)
    {
        return $this->activeManualInspectionServiceBaseQuery()
            ->whereDate(self::DATE_FIELD, $normalizedDate);
    }

    private function activeManualInspectionServiceBaseQuery()
    {
        return ServiceRequest::query()
            ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
            ->where(self::REQUEST_TYPE_FIELD, ServiceRequest::MANUAL_INSPECTION_REQUEST_TYPE);
    }

    private function acquireDateLocks(array $dates, Closure $callback, int $timeoutSeconds): mixed
    {
        if ($dates === []) {
            return $callback();
        }

        $date = array_shift($dates);
        $lock = Cache::lock($this->lockKey($date), self::LOCK_TTL_SECONDS);

        try {
            return $lock->block($timeoutSeconds, function () use ($dates, $callback, $timeoutSeconds) {
                return $this->acquireDateLocks($dates, $callback, $timeoutSeconds);
            });
        } catch (LockTimeoutException) {
            throw $this->reservedDateValidationException();
        }
    }

    private function lockKey(string $preferredDate): string
    {
        return "preferred-date-lock:{$preferredDate}";
    }

    private function reservedDateValidationException(): ValidationException
    {
        return ValidationException::withMessages([
            self::DATE_FIELD => ['Selected date is already reserved. Please choose another date.'],
        ]);
    }
}

<?php

namespace App\Services;

use App\Models\InspectionRequest;
use App\Models\ServiceRequest;
use Closure;
use DateTimeInterface;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Database\Eloquent\Model;
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

    private const DATE_FIELD = 'date_needed';

    private const STATUS_FIELD = 'status';

    private const LOCK_TTL_SECONDS = 10;

    private const LOCK_WAIT_SECONDS = 5;

    public function ensureDateIsAvailable(
        DateTimeInterface|string|null $preferredDate,
        ?int $excludeRecordId = null,
        ?string $excludeModelClass = null
    ): void {
        if (! $this->isDateLocked($preferredDate, $excludeRecordId, $excludeModelClass)) {
            return;
        }

        throw $this->reservedDateValidationException();
    }

    public function isDateLocked(
        DateTimeInterface|string|null $preferredDate,
        ?int $excludeRecordId = null,
        ?string $excludeModelClass = null
    ): bool {
        if ($preferredDate === null || $preferredDate === '') {
            return false;
        }

        $normalizedDate = $this->normalizeDate($preferredDate);

        foreach ($this->lockingModels() as $modelClass) {
            $query = $modelClass::query()
                ->whereDate(self::DATE_FIELD, $normalizedDate)
                ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES);

            if ($excludeRecordId !== null && $excludeModelClass === $modelClass) {
                $query->whereKeyNot($excludeRecordId);
            }

            if ($query->exists()) {
                return true;
            }
        }

        return false;
    }

    public function getUnavailableDates(): array
    {
        $dates = [];

        foreach ($this->lockingModels() as $modelClass) {
            $modelDates = $modelClass::query()
                ->whereIn(self::STATUS_FIELD, self::ACTIVE_LOCK_STATUSES)
                ->whereNotNull(self::DATE_FIELD)
                ->pluck(self::DATE_FIELD)
                ->all();

            foreach ($modelDates as $date) {
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

    /**
     * @return array<class-string<Model>>
     */
    private function lockingModels(): array
    {
        return [
            InspectionRequest::class,
            ServiceRequest::class,
        ];
    }

    private function normalizeDate(DateTimeInterface|string $preferredDate): string
    {
        return Carbon::parse($preferredDate)->toDateString();
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

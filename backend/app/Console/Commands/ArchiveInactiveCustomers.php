<?php

namespace App\Console\Commands;

use App\Models\CustomerArchiveAudit;
use App\Models\User;
use App\Notifications\CustomerArchiveWarningNotification;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ArchiveInactiveCustomers extends Command
{
    protected $signature = 'customers:archive-inactive';

    protected $description = 'Archive inactive customer accounts and notify customers before archival.';

    public function handle(): int
    {
        $inactivityDays = max(1, (int) config('customer_archiving.inactivity_days', 60));
        $warningDays = max(0, min($inactivityDays - 1, (int) config('customer_archiving.warning_days', 7)));
        $chunkSize = max(1, (int) config('customer_archiving.chunk_size', 200));

        $now = now();
        $archiveCutoff = $now->copy()->subDays($inactivityDays);
        $warningCutoff = $warningDays > 0
            ? $now->copy()->subDays($inactivityDays - $warningDays)
            : null;

        $warningCount = $warningCutoff ? $this->sendWarnings($warningCutoff, $archiveCutoff, $inactivityDays, $chunkSize) : 0;
        $archiveCount = $this->archiveCustomers($archiveCutoff, $inactivityDays, $chunkSize);

        Log::info('Inactive customer archival run completed.', [
            'warning_count' => $warningCount,
            'archive_count' => $archiveCount,
            'inactivity_days' => $inactivityDays,
            'warning_days' => $warningDays,
            'chunk_size' => $chunkSize,
            'executed_at' => $now->toDateTimeString(),
        ]);

        $this->info("Inactive customer archival complete. Warnings sent: {$warningCount}. Archived: {$archiveCount}.");

        return self::SUCCESS;
    }

    private function sendWarnings(Carbon $warningCutoff, Carbon $archiveCutoff, int $inactivityDays, int $chunkSize): int
    {
        $sentCount = 0;

        $this->activeCustomerQuery()
            ->whereNull('archive_warning_sent_at')
            ->whereRaw('COALESCE(last_login_at, created_at) <= ?', [$warningCutoff])
            ->whereRaw('COALESCE(last_login_at, created_at) > ?', [$archiveCutoff])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($customers) use (&$sentCount, $inactivityDays): void {
                foreach ($customers as $customer) {
                    if ($customer->isArchivedCustomer()) {
                        continue;
                    }

                    $inactiveSince = Carbon::parse($customer->last_login_at ?? $customer->created_at);
                    $archiveOn = $inactiveSince->copy()->addDays($inactivityDays);

                    $customer->notify(new CustomerArchiveWarningNotification($archiveOn, $inactiveSince));
                    $customer->forceFill([
                        'archive_warning_sent_at' => now(),
                    ])->save();

                    CustomerArchiveAudit::query()->create([
                        'user_id' => $customer->id,
                        'action' => 'warning_sent',
                        'reason' => 'pending_inactivity_archive',
                        'context' => [
                            'inactive_since' => $inactiveSince->toDateTimeString(),
                            'archive_on' => $archiveOn->toDateTimeString(),
                        ],
                    ]);

                    $sentCount++;
                }
            });

        return $sentCount;
    }

    private function archiveCustomers(Carbon $archiveCutoff, int $inactivityDays, int $chunkSize): int
    {
        $archivedCount = 0;

        $this->activeCustomerQuery()
            ->whereRaw('COALESCE(last_login_at, created_at) <= ?', [$archiveCutoff])
            ->orderBy('id')
            ->chunkById($chunkSize, function ($customers) use (&$archivedCount, $archiveCutoff, $inactivityDays): void {
                foreach ($customers as $customer) {
                    if ($customer->isArchivedCustomer()) {
                        continue;
                    }

                    $inactiveSince = Carbon::parse($customer->last_login_at ?? $customer->created_at);

                    $customer->archiveAccount(
                        reason: 'inactive_for_'.$inactivityDays.'_days',
                        context: [
                            'inactive_since' => $inactiveSince->toDateTimeString(),
                            'archive_cutoff' => $archiveCutoff->toDateTimeString(),
                        ],
                    );

                    $archivedCount++;
                }
            });

        return $archivedCount;
    }

    private function activeCustomerQuery(): Builder
    {
        return User::query()
            ->where('role', User::ROLE_CUSTOMER)
            ->whereNull('archived_at')
            ->where('is_archived', false);
    }
}
<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

abstract class BaseDatabaseNotification extends Notification
{
    use Queueable;

    public function __construct(protected ?int $createdBy = null)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    protected function buildPayload(array $payload): array
    {
        return array_merge([
            'type' => null,
            'title' => null,
            'message' => null,
            'entity_type' => null,
            'entity_id' => null,
            'target_screen' => null,
            'target_params' => [],
            'status' => null,
            'created_by' => $this->createdBy,
            'created_at_display' => now()->format('M d, Y h:i A'),
        ], $payload);
    }

    protected function formatDate(null|string|Carbon $value): ?string
    {
        if ($value === null) {
            return null;
        }

        return ($value instanceof Carbon ? $value : Carbon::parse($value))->format('M d, Y');
    }
}

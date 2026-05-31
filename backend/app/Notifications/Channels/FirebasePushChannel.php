<?php

namespace App\Notifications\Channels;

use App\Services\FirebaseNotificationService;

class FirebasePushChannel
{
    public function __construct(
        private readonly FirebaseNotificationService $pushNotifications,
    ) {}

    public function send(object $notifiable, object $notification): void
    {
        $token = trim((string) ($notifiable->fcm_token ?? ''));

        if ($token === '' || ! method_exists($notification, 'toArray')) {
            return;
        }

        $payload = $notification->toArray($notifiable);
        $title = trim((string) ($payload['title'] ?? ''));
        $message = trim((string) ($payload['message'] ?? ''));

        if ($title === '' || $message === '') {
            return;
        }

        $this->pushNotifications->sendNotification($token, $title, $message);
    }
}
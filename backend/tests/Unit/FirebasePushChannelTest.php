<?php

namespace Tests\Unit;

use App\Models\User;
use App\Notifications\BaseDatabaseNotification;
use App\Notifications\Channels\FirebasePushChannel;
use App\Services\FirebaseNotificationService;
use PHPUnit\Framework\TestCase;

class FirebasePushChannelTest extends TestCase
{
    public function test_it_sends_push_notifications_for_database_notifications_when_a_token_exists(): void
    {
        $pushNotifications = $this->createMock(FirebaseNotificationService::class);

        $pushNotifications->expects($this->once())
            ->method('sendNotification')
            ->with(
                'firebase-device-token',
                'Inspection Request Updated',
                'Your inspection request status has been updated to Completed.'
            );

        $channel = new FirebasePushChannel($pushNotifications);
        $user = new User(['fcm_token' => 'firebase-device-token']);

        $notification = new class extends BaseDatabaseNotification
        {
            public function toArray(object $notifiable): array
            {
                return $this->buildPayload([
                    'title' => 'Inspection Request Updated',
                    'message' => 'Your inspection request status has been updated to Completed.',
                ]);
            }
        };

        $channel->send($user, $notification);
    }

    public function test_it_skips_push_notifications_when_the_user_has_no_fcm_token(): void
    {
        $pushNotifications = $this->createMock(FirebaseNotificationService::class);

        $pushNotifications->expects($this->never())
            ->method('sendNotification');

        $channel = new FirebasePushChannel($pushNotifications);
        $user = new User(['fcm_token' => null]);

        $notification = new class extends BaseDatabaseNotification
        {
            public function toArray(object $notifiable): array
            {
                return $this->buildPayload([
                    'title' => 'Inspection Request Updated',
                    'message' => 'Your inspection request status has been updated to Completed.',
                ]);
            }
        };

        $channel->send($user, $notification);
    }
}
<?php

namespace Tests\Unit;

use App\Services\FirebaseNotificationService;
use Illuminate\Config\Repository;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Messaging\CloudMessage;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class FirebaseNotificationServiceTest extends TestCase
{
    public function test_it_sends_a_notification_with_the_expected_payload(): void
    {
        $messaging = $this->createMock(Messaging::class);

        $messaging->expects($this->once())
            ->method('send')
            ->with($this->callback(function (CloudMessage $message): bool {
                $payload = $message->jsonSerialize();

                return $payload['token'] === 'device-token'
                    && $payload['notification']['title'] === 'Booking Confirmed'
                    && $payload['notification']['body'] === 'Your inspection is scheduled';
            }))
            ->willReturn(['name' => 'projects/demo/messages/123']);

        $service = new FirebaseNotificationService(new Repository([]), $messaging);

        $result = $service->sendNotification(
            'device-token',
            'Booking Confirmed',
            'Your inspection is scheduled'
        );

        $this->assertSame(['name' => 'projects/demo/messages/123'], $result);
    }

    public function test_it_returns_null_when_sending_fails(): void
    {
        $messaging = $this->createMock(Messaging::class);

        $messaging->expects($this->once())
            ->method('send')
            ->willThrowException(new RuntimeException('FCM send failed'));

        $service = new FirebaseNotificationService(new Repository([]), $messaging);

        $result = $service->sendNotification(
            'device-token',
            'Booking Confirmed',
            'Your inspection is scheduled'
        );

        $this->assertNull($result);
    }
}
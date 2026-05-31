<?php

namespace App\Services;

use Illuminate\Contracts\Config\Repository;
use InvalidArgumentException;
use Kreait\Firebase\Contract\Messaging;
use Kreait\Firebase\Exception\FirebaseException;
use Kreait\Firebase\Exception\MessagingException;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Throwable;

class FirebaseNotificationService
{
    public function __construct(
        private readonly Repository $config,
        private ?Messaging $messaging = null,
        private readonly ?LoggerInterface $logger = null,
    ) {}

    /**
     * @return array<array-key, mixed>|null
     */
    public function sendNotification(string $deviceToken, string $title, string $body): ?array
    {
        $deviceToken = trim($deviceToken);
        $title = trim($title);
        $body = trim($body);

        if ($deviceToken === '') {
            throw new InvalidArgumentException('A device token is required to send a Firebase notification.');
        }

        if ($title === '' || $body === '') {
            throw new InvalidArgumentException('Firebase notifications require both a title and a body.');
        }

        $messaging = $this->messaging();

        $message = CloudMessage::new()
            ->withToken($deviceToken)
            ->withNotification(Notification::create($title, $body));

        try {
            return $messaging->send($message);
        } catch (MessagingException|FirebaseException|Throwable $exception) {
            $this->logger?->error('Failed to send Firebase push notification.', [
                'title' => $title,
                'token_suffix' => $this->tokenSuffix($deviceToken),
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function messaging(): Messaging
    {
        if ($this->messaging instanceof Messaging) {
            return $this->messaging;
        }

        $factory = (new Factory())
            ->withServiceAccount($this->credentialsPath());

        return $this->messaging = $factory->createMessaging();
    }

    private function credentialsPath(): string
    {
        $configuredPath = trim((string) $this->config->get('services.firebase.credentials'));

        if ($configuredPath === '') {
            throw new RuntimeException('Firebase credentials are not configured. Set FIREBASE_CREDENTIALS in the environment.');
        }

        $resolvedPath = $this->isAbsolutePath($configuredPath)
            ? $configuredPath
            : base_path($configuredPath);

        if (! is_file($resolvedPath)) {
            throw new RuntimeException("Firebase credentials file not found at [{$resolvedPath}].");
        }

        return $resolvedPath;
    }

    private function isAbsolutePath(string $path): bool
    {
        return str_starts_with($path, DIRECTORY_SEPARATOR)
            || preg_match('/^[A-Za-z]:[\\\\\/]/', $path) === 1;
    }

    private function tokenSuffix(string $deviceToken): string
    {
        return substr($deviceToken, -8) ?: $deviceToken;
    }
}
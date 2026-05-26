<?php

namespace App\Notifications;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;

class CustomerArchiveWarningNotification extends BaseDatabaseNotification implements ShouldQueue
{
    public function __construct(
        private readonly Carbon $archiveOn,
        private readonly ?Carbon $inactiveSince = null,
        ?int $createdBy = null
    ) {
        parent::__construct($createdBy);
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toArray(object $notifiable): array
    {
        $inactiveSince = $this->inactiveSince?->format('M d, Y');

        return $this->buildPayload([
            'type' => 'customer_archive_warning',
            'title' => 'Account Archiving Warning',
            'message' => $inactiveSince
                ? "We haven't seen a login since {$inactiveSince}. Your account will be archived on {$this->archiveOn->format('M d, Y')} unless you sign in first."
                : "Your account will be archived on {$this->archiveOn->format('M d, Y')} unless you sign in first.",
            'entity_type' => 'user',
            'entity_id' => $notifiable->id ?? null,
            'target_screen' => 'Login',
            'target_params' => [],
            'status' => 'warning',
        ]);
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mailMessage = (new MailMessage)
            ->subject('SolMate account inactivity warning')
            ->greeting('Hello '.$notifiable->name.',')
            ->line('Your SolMate account is scheduled for archival because it has been inactive.')
            ->line('Archive date: '.$this->archiveOn->format('M d, Y'));

        if ($this->inactiveSince !== null) {
            $mailMessage->line('Last activity reference: '.$this->inactiveSince->format('M d, Y'));
        }

        return $mailMessage
            ->line('Sign in before that date to keep your account active.')
            ->line('If you need help, please contact support.');
    }
}
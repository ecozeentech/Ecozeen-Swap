<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Deliberately NOT queued: this notification's 'database' channel is what
 * powers the in-app notification bell, and on shared hosting the queue
 * worker only runs once a minute (or not at all if the cron job wasn't
 * set up). Queuing would leave the bell showing nothing until the next
 * cron tick. Writing a single notifications row is cheap, so both
 * channels — including 'mail' — run synchronously within the request.
 */
class KycStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(protected string $status, protected ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Your KYC verification status has been updated')
            ->greeting('Hi '.$notifiable->name.',');

        if ($this->status === 'verified') {
            return $message
                ->line('Congratulations! Your identity verification has been approved.')
                ->line('Your daily trading limits have now been increased.')
                ->action('Go to Dashboard', route('dashboard'));
        }

        return $message
            ->line('Unfortunately, your KYC submission was rejected.')
            ->when($this->reason, fn ($m) => $m->line('Reason: '.$this->reason))
            ->action('Resubmit Documents', route('security.kyc'));
    }

    public function toArray(object $notifiable): array
    {
        if ($this->status === 'verified') {
            return [
                'title' => 'KYC Verified',
                'message' => 'Your identity verification was approved. Your daily trading limit has increased.',
                'level' => 'success',
                'url' => route('security.kyc'),
            ];
        }

        return [
            'title' => 'KYC Rejected',
            'message' => 'Your KYC submission was rejected.'.($this->reason ? ' Reason: '.$this->reason : ''),
            'level' => 'danger',
            'url' => route('security.kyc'),
        ];
    }
}

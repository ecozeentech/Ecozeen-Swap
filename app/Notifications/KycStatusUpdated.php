<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class KycStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected string $status, protected ?string $reason = null) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
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
}

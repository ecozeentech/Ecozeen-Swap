<?php

namespace App\Notifications;

use App\Models\UserTrustedIp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewIpDetected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected UserTrustedIp $trustedIp) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = route('security.verify-ip', [
            'trustedIp' => $this->trustedIp->id,
            'code' => $this->trustedIp->verification_code,
        ]);

        return (new MailMessage)
            ->subject('New sign-in location detected — Ecozeen Swap')
            ->greeting('Hi '.$notifiable->name.',')
            ->line('We noticed a sign-in to your Ecozeen Swap account from a new IP address: '.$this->trustedIp->ip_address)
            ->line('Trading has been temporarily restricted on this device until you confirm it was you.')
            ->action('Confirm this device', $url)
            ->line('If this was not you, please change your password immediately and contact support.');
    }
}

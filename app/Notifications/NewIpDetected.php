<?php

namespace App\Notifications;

use App\Models\UserTrustedIp;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Deliberately NOT queued — see KycStatusUpdated for why. The in-app
 * notification bell relies on the 'database' channel firing immediately.
 */
class NewIpDetected extends Notification
{
    use Queueable;

    public function __construct(protected UserTrustedIp $trustedIp) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
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

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'New Sign-in Detected',
            'message' => 'A sign-in was detected from a new IP address: '.$this->trustedIp->ip_address.'. Confirm it was you to lift trading restrictions on this device.',
            'level' => 'warning',
            'url' => route('security.verify-ip', [
                'trustedIp' => $this->trustedIp->id,
                'code' => $this->trustedIp->verification_code,
            ]),
        ];
    }
}

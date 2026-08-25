<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * A single, general-purpose database notification used for every
 * account-level event a user should be alerted about (KYC decisions,
 * transaction status changes, gift card verification, security alerts,
 * etc.) instead of a dozen near-identical notification classes.
 */
class AccountNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected string $title,
        protected string $message,
        protected string $level = 'info', // info | success | warning | danger
        protected ?string $url = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'message' => $this->message,
            'level' => $this->level,
            'url' => $this->url,
        ];
    }
}

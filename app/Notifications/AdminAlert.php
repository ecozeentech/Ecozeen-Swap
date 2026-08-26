<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\Notify;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * A database notification sent to every admin/super-admin when a user
 * does something that needs admin attention (new registration, KYC
 * submission, gift card submission, deposit proof upload, withdrawal
 * request, sell awaiting confirmation, etc.). Powers the notification
 * bell shown on the admin dashboard.
 *
 * Deliberately not queued — see AccountNotification for why.
 */
class AdminAlert extends Notification
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

    /**
     * Notify every admin/super-admin user at once.
     */
    public static function broadcast(string $title, string $message, string $level = 'info', ?string $url = null): void
    {
        $admins = User::role(['admin', 'super-admin'])->get();

        if ($admins->isEmpty()) {
            return;
        }

        Notify::send($admins, new self($title, $message, $level, $url));
    }
}

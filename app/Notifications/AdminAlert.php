<?php

namespace App\Notifications;

use App\Models\User;
use App\Support\Notify;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
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
        // Admins need to know about new orders/withdrawals/KYC submissions
        // immediately, not only the next time they happen to open the
        // admin panel and check the bell — so this goes to email too.
        // Notify::send() already swallows any mail delivery failure (see
        // its docblock), so a broken/unset SMTP config never blocks the
        // action that triggered this alert.
        return ['database', 'mail'];
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

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('['.config('app.name').'] '.$this->title)
            ->greeting($this->title)
            ->line($this->message);

        if ($this->url) {
            $mail->action('Review in Admin Panel', $this->url);
        }

        return $mail;
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

<?php

namespace App\Support;

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Throwable;

/**
 * Every notification in this app is sent synchronously (see
 * KycStatusUpdated for why), which means a mail delivery failure —
 * wrong SMTP credentials, the provider being briefly unreachable, a
 * mistyped host while an admin is still setting things up — would
 * otherwise throw and 500 the request that triggered it. Approving a
 * KYC document or marking a transaction paid must always succeed
 * regardless of whether the "we emailed the user" side-effect worked, so
 * every notify() call in the app goes through here instead of calling
 * ->notify() directly: failures are logged instead of thrown.
 */
class Notify
{
    /**
     * @param  Dispatcher|mixed  $notifiable  a single notifiable or a collection of them
     */
    public static function send(mixed $notifiable, Notification $notification): void
    {
        try {
            if ($notifiable instanceof Collection || is_array($notifiable)) {
                NotificationFacade::send($notifiable, $notification);
            } else {
                $notifiable->notify($notification);
            }
        } catch (Throwable $e) {
            Log::warning('Notification delivery failed: '.get_class($notification).' — '.$e->getMessage());
        }
    }
}

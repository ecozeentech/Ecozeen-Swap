<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TransactionStatusUpdated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Transaction update — '.$this->transaction->reference)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Your '.$this->transaction->type.' transaction of '.$this->transaction->amount.' '.$this->transaction->currency_code.' is now '.$this->transaction->status.'.')
            ->action('View Transaction', route('wallet.index'));
    }
}

<?php

namespace App\Notifications;

use App\Models\Transaction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Deliberately NOT queued — see KycStatusUpdated for why. The in-app
 * notification bell relies on the 'database' channel firing immediately.
 */
class TransactionStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(protected Transaction $transaction) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Transaction update — '.$this->transaction->reference)
            ->greeting('Hi '.$notifiable->name.',')
            ->line('Your '.$this->transaction->type.' transaction of '.$this->transaction->amount.' '.$this->transaction->currency_code.' is now '.$this->transaction->status.'.')
            ->action('View Transaction', route('wallet.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => ucfirst($this->transaction->type).' '.($this->transaction->status === 'completed' ? 'Completed' : ucfirst($this->transaction->status)),
            'message' => 'Your '.$this->transaction->type.' of '.number_format((float) $this->transaction->amount, 4).' '.$this->transaction->currency_code.' is now '.$this->transaction->status.'. Ref: '.$this->transaction->reference,
            'level' => match ($this->transaction->status) {
                'completed' => 'success',
                'failed', 'cancelled' => 'danger',
                default => 'info',
            },
            'url' => route('wallet.index'),
        ];
    }
}

<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * A quick way to prove SMTP is actually configured and working, without
 * needing to trigger a real registration/KYC/transaction event first.
 * Laravel doesn't ship a built-in equivalent.
 */
class TestMailCommand extends Command
{
    protected $signature = 'mail:test {email : The address to send a test message to}';

    protected $description = 'Send a simple test email to verify the configured mail driver actually works';

    public function handle(): int
    {
        $to = $this->argument('email');
        $mailer = config('mail.default');

        $this->info("Sending a test email to {$to} using the '{$mailer}' mailer...");

        try {
            Mail::raw(
                'This is a test email from '.config('app.name').".\n\nIf you're reading this, your SMTP configuration is working correctly.\n\nSent at ".now()->toDateTimeString(),
                function ($message) use ($to) {
                    $message->to($to)->subject('Test Email — '.config('app.name'));
                }
            );
        } catch (TransportExceptionInterface $e) {
            $this->error('Failed to send: '.$e->getMessage());
            $this->line('');
            $this->line('Common causes: wrong MAIL_HOST/MAIL_PORT, wrong MAIL_USERNAME/MAIL_PASSWORD, the port is blocked by your host/firewall, or MAIL_ENCRYPTION/MAIL_SCHEME does not match what your provider expects (try "tls" on port 587 or "ssl" on port 465).');

            return self::FAILURE;
        }

        if ($mailer === 'log') {
            $this->warn("MAIL_MAILER is still set to 'log' — no real email was sent, it was only written to storage/logs/laravel.log. Set MAIL_MAILER=smtp in .env, then run `php artisan config:clear` and try again.");

            return self::SUCCESS;
        }

        $this->info('Sent without a transport error. Check the inbox (and spam folder) at '.$to.' to confirm it actually arrived.');

        return self::SUCCESS;
    }
}

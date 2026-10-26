<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\SystemSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Throwable;

/**
 * Lets an admin configure the SMTP credentials used for every outgoing
 * email (order/withdrawal alerts, KYC decisions, account notifications)
 * without touching the server's .env file or redeploying. See
 * AppServiceProvider::configureMailFromSettings(), which applies these
 * settings to the runtime mail config on every request — falling back to
 * whatever is in .env for anything left unset here.
 */
class MailSettingsController extends Controller
{
    public function edit(): View
    {
        return view('admin.mail-settings.edit', [
            'settings' => SystemSetting::allSettings(),
            'hasStoredPassword' => (bool) SystemSetting::get('mail_password'),
            'effectiveMailer' => config('mail.default'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'mail_mailer' => ['required', 'in:smtp,log'],
            'mail_host' => ['required_if:mail_mailer,smtp', 'nullable', 'string', 'max:255'],
            'mail_port' => ['required_if:mail_mailer,smtp', 'nullable', 'integer', 'min:1', 'max:65535'],
            'mail_username' => ['nullable', 'string', 'max:255'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_encryption' => ['required', 'in:tls,ssl,none'],
            'mail_from_address' => ['required', 'email', 'max:255'],
            'mail_from_name' => ['required', 'string', 'max:255'],
        ]);

        SystemSetting::set('mail_mailer', $request->input('mail_mailer'), 'string', 'mail');
        SystemSetting::set('mail_host', $request->input('mail_host', ''), 'string', 'mail');
        SystemSetting::set('mail_port', $request->input('mail_port', ''), 'string', 'mail');
        SystemSetting::set('mail_username', $request->input('mail_username', ''), 'string', 'mail');
        SystemSetting::set('mail_encryption', $request->input('mail_encryption'), 'string', 'mail');
        SystemSetting::set('mail_from_address', $request->input('mail_from_address'), 'string', 'mail');
        SystemSetting::set('mail_from_name', $request->input('mail_from_name'), 'string', 'mail');

        // Leave the stored password untouched when the field is left
        // blank — the edit form never re-displays the real value, so a
        // blank submission means "keep what's already saved", not
        // "clear it".
        if ($request->filled('mail_password')) {
            SystemSetting::set('mail_password', $request->input('mail_password'), 'encrypted', 'mail');
        }

        ActivityLog::record(auth()->id(), 'admin_updated_mail_settings');

        return back()->with('status', 'mail-settings-updated');
    }

    public function sendTest(Request $request): RedirectResponse
    {
        $request->validate(['test_email' => ['required', 'email']]);

        try {
            Mail::raw(
                'This is a test email from '.config('app.name').".\n\nIf you're reading this, your SMTP configuration (set from Admin > Mail Settings) is working correctly.\n\nSent at ".now()->toDateTimeString(),
                fn ($message) => $message->to($request->input('test_email'))->subject('Test Email — '.config('app.name'))
            );
        } catch (TransportExceptionInterface|Throwable $e) {
            return back()->withErrors(['test_email' => 'Failed to send: '.$e->getMessage()]);
        }

        if (config('mail.default') === 'log') {
            return back()->withErrors(['test_email' => "The mailer is set to 'Log (testing only)' — no real email was sent, it was only written to the server log. Switch to SMTP above to send real emails."]);
        }

        return back()->with('status', 'test-email-sent');
    }
}

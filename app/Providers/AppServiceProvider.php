<?php

namespace App\Providers;

use App\Models\SystemSetting;
use App\View\Composers\GlobalSettingsComposer;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('trade', function (Request $request) {
            return Limit::perMinute(10)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('withdraw', function (Request $request) {
            return Limit::perMinute(5)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('webhooks', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        View::composer(['layouts.app', 'layouts.admin', 'layouts.guest'], GlobalSettingsComposer::class);

        $this->configureMailFromSettings();
    }

    /**
     * Lets Admin > Mail Settings fully override the SMTP credentials
     * normally supplied via .env, with no code change or redeploy
     * needed. Falls back to whatever is already in config/mail.php (set
     * from .env) for anything an admin hasn't configured yet, so a site
     * that only ever used .env keeps working exactly as before.
     */
    protected function configureMailFromSettings(): void
    {
        try {
            if (! Schema::hasTable('system_settings')) {
                return;
            }

            $mailer = SystemSetting::get('mail_mailer');
        } catch (Throwable $e) {
            // Database not reachable yet (e.g. mid-`migrate`) — just use
            // whatever .env/config already supplied.
            return;
        }

        if (! $mailer) {
            return;
        }

        config(['mail.default' => $mailer]);

        if ($mailer !== 'smtp') {
            return;
        }

        $encryption = SystemSetting::get('mail_encryption');

        config([
            'mail.mailers.smtp.host' => SystemSetting::get('mail_host') ?: config('mail.mailers.smtp.host'),
            'mail.mailers.smtp.port' => SystemSetting::get('mail_port') ?: config('mail.mailers.smtp.port'),
            'mail.mailers.smtp.username' => SystemSetting::get('mail_username') ?: config('mail.mailers.smtp.username'),
            'mail.mailers.smtp.password' => SystemSetting::get('mail_password') ?: config('mail.mailers.smtp.password'),
            // Symfony Mailer uses 'scheme' (not 'encryption') to pick implicit
            // TLS: 'smtps' for port-465-style implicit TLS, left unset for
            // STARTTLS (the common port-587 case) or no encryption at all.
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : null,
            'mail.from.address' => SystemSetting::get('mail_from_address') ?: config('mail.from.address'),
            'mail.from.name' => SystemSetting::get('mail_from_name') ?: config('mail.from.name'),
        ]);
    }
}

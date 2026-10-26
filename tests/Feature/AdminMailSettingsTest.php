<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AdminMailSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_system_setting_encrypts_and_decrypts_a_value(): void
    {
        SystemSetting::set('mail_password', 'super-secret', 'encrypted', 'mail');

        $raw = \DB::table('system_settings')->where('key', 'mail_password')->first();
        $this->assertNotSame('super-secret', $raw->value);
        $this->assertStringContainsString('eyJpdiI6', $raw->value); // Laravel's encrypted payload shape

        $this->assertSame('super-secret', SystemSetting::get('mail_password'));
    }

    public function test_admin_can_save_smtp_settings(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->put(route('admin.mail-settings.update'), [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.example.com',
            'mail_port' => 587,
            'mail_username' => 'postmaster@example.com',
            'mail_password' => 'secret-password',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Ecozeen Swap',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $this->assertSame('smtp', SystemSetting::get('mail_mailer'));
        $this->assertSame('smtp.example.com', SystemSetting::get('mail_host'));
        $this->assertSame('secret-password', SystemSetting::get('mail_password'));
        $this->assertSame('noreply@example.com', SystemSetting::get('mail_from_address'));
    }

    public function test_saving_settings_with_a_blank_password_keeps_the_existing_one(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        SystemSetting::set('mail_password', 'original-password', 'encrypted', 'mail');

        $this->actingAs($admin)->put(route('admin.mail-settings.update'), [
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.example.com',
            'mail_port' => 587,
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Ecozeen Swap',
            // mail_password intentionally omitted
        ]);

        $this->assertSame('original-password', SystemSetting::get('mail_password'));
    }

    public function test_smtp_mailer_requires_host_and_port(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->put(route('admin.mail-settings.update'), [
            'mail_mailer' => 'smtp',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@example.com',
            'mail_from_name' => 'Ecozeen Swap',
        ]);

        $response->assertSessionHasErrors(['mail_host', 'mail_port']);
    }

    public function test_sending_a_test_email_warns_instead_of_claiming_success_on_the_log_driver(): void
    {
        config(['mail.default' => 'log']);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->post(route('admin.mail-settings.test'), [
            'test_email' => 'someone@example.com',
        ]);

        // The log driver "succeeds" (no transport exception) but the
        // controller surfaces a warning since nothing was really sent.
        $response->assertSessionHasErrors('test_email');
    }

    public function test_sending_a_test_email_succeeds_with_a_real_transport(): void
    {
        Mail::fake();
        config(['mail.default' => 'smtp']);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->post(route('admin.mail-settings.test'), [
            'test_email' => 'someone@example.com',
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('status', 'test-email-sent');
    }

    public function test_non_admin_cannot_access_mail_settings(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.mail-settings.edit'))->assertForbidden();
    }

    public function test_configured_smtp_settings_override_the_runtime_mail_config(): void
    {
        SystemSetting::set('mail_mailer', 'smtp', 'string', 'mail');
        SystemSetting::set('mail_host', 'custom.smtp.test', 'string', 'mail');
        SystemSetting::set('mail_port', '2525', 'string', 'mail');
        SystemSetting::set('mail_encryption', 'ssl', 'string', 'mail');
        SystemSetting::set('mail_from_address', 'custom@example.com', 'string', 'mail');

        (new AppServiceProvider(app()))->boot();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('custom.smtp.test', config('mail.mailers.smtp.host'));
        $this->assertSame('2525', config('mail.mailers.smtp.port'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('custom@example.com', config('mail.from.address'));
    }
}

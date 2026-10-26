<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\AdminAlert;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AdminEmailNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_alert_is_delivered_by_both_mail_and_database(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $alert = new AdminAlert('New Order', 'A user placed an order.', 'info', 'https://example.test/admin');

        $this->assertEqualsCanonicalizing(['database', 'mail'], $alert->via($admin));
    }

    public function test_admin_alert_mail_message_includes_title_message_and_action_link(): void
    {
        $alert = new AdminAlert('Withdrawal Requested', 'Jane requested $100.', 'warning', 'https://example.test/admin/withdrawals');
        $mail = $alert->toMail(User::factory()->make());

        $this->assertSame('[Ecozeen Swap] Withdrawal Requested', $mail->subject);
        $rendered = $mail->render();
        $this->assertStringContainsString('Jane requested $100.', $rendered);
        $this->assertStringContainsString('https://example.test/admin/withdrawals', $rendered);
    }

    public function test_broadcasting_an_admin_alert_notifies_every_admin_via_mail_and_database(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $regularUser = User::factory()->create();

        AdminAlert::broadcast('New Sell Order', 'A sell order was placed.', 'warning', 'https://example.test');

        Notification::assertSentTo($admin, AdminAlert::class, function (AdminAlert $notification, array $channels) {
            return in_array('mail', $channels, true) && in_array('database', $channels, true);
        });
        Notification::assertNotSentTo($regularUser, AdminAlert::class);
    }
}

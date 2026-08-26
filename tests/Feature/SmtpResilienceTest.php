<?php

namespace Tests\Feature;

use App\Models\KycDocument;
use App\Models\User;
use App\Notifications\KycStatusUpdated;
use App\Support\Notify;
use Illuminate\Contracts\Mail\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

/**
 * A mail delivery failure (bad SMTP credentials, provider outage, still
 * mid-setup, etc.) must never prevent the core business action — e.g.
 * approving KYC — from completing, and must never turn the request into
 * a 500. See App\Support\Notify for the fix.
 */
class SmtpResilienceTest extends TestCase
{
    use RefreshDatabase;

    protected function simulateBrokenMailTransport(): void
    {
        $this->app->bind(Factory::class, function () {
            return new class implements Factory
            {
                public function mailer($name = null)
                {
                    throw new RuntimeException('Simulated SMTP failure');
                }
            };
        });
    }

    public function test_notify_send_swallows_a_mail_transport_failure(): void
    {
        $this->simulateBrokenMailTransport();

        $user = User::factory()->create();

        Notify::send($user, new KycStatusUpdated('verified'));

        $this->assertTrue(true); // reaching this line means no exception propagated
    }

    public function test_approving_kyc_still_succeeds_when_mail_delivery_fails(): void
    {
        $this->simulateBrokenMailTransport();

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create(['kyc_status' => 'pending']);
        $document = KycDocument::create([
            'user_id' => $user->id, 'document_type' => 'id_card', 'file_path' => 'kyc/x.jpg', 'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->post(route('admin.kyc.approve', $document));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'kyc-approved');
        $this->assertSame('verified', $user->fresh()->kyc_status);
    }
}

<?php

namespace Tests\Feature;

use App\Livewire\NotificationBell;
use App\Models\FiatCurrency;
use App\Models\GiftCard;
use App\Models\PaymentGateway;
use App\Models\SystemSetting;
use App\Models\User;
use App\Notifications\AccountNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class PlatformFixesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_gateways_page_does_not_500_when_credentials_cannot_be_decrypted(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $gateway = PaymentGateway::create([
            'name' => 'Paystack', 'slug' => 'paystack', 'is_active' => false,
            'credentials' => ['public_key' => 'pk_test', 'secret_key' => 'sk_test'],
            'metadata' => [],
        ]);

        // Simulate the real-world failure mode: APP_KEY rotated (or a DB
        // dump restored into a different environment) after the value was
        // encrypted, so it can no longer be decrypted with the current key.
        DB::table('payment_gateways')->where('id', $gateway->id)->update([
            'credentials' => 'not-a-valid-encrypted-payload',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.gateways.index'));

        $response->assertOk();
        $this->assertSame([], $gateway->fresh()->credentials);
    }

    public function test_branding_reset_uses_delete_method_and_succeeds(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        SystemSetting::set('site_logo', 'branding/some-logo.png', 'string', 'branding');

        // This previously 405'd because the reset <form> was nested
        // inside the outer upload <form>, which is invalid HTML.
        $response = $this->actingAs($admin)->delete(route('admin.branding.reset', 'site_logo'));

        $response->assertRedirect();
        $response->assertSessionHas('status', 'branding-reset');
    }

    public function test_branding_edit_view_does_not_nest_forms(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        SystemSetting::set('site_logo', 'branding/some-logo.png', 'string', 'branding');

        $html = $this->actingAs($admin)->get(route('admin.branding.edit'))->getContent();

        // Crude but effective nested-<form> detector: track form depth as
        // we scan tags in document order.
        $depth = 0;
        preg_match_all('/<\/?form\b[^>]*>/i', $html, $matches);
        foreach ($matches[0] as $tag) {
            if (str_starts_with($tag, '</')) {
                $depth--;
            } else {
                $depth++;
                $this->assertLessThanOrEqual(1, $depth, 'Found a <form> nested inside another <form>.');
            }
        }
    }

    public function test_fiat_currency_accepts_svg_logo_uploads(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $svg = UploadedFile::fake()->createWithContent(
            'naira.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" width="10" height="10"></svg>'
        );

        $response = $this->actingAs($admin)->post(route('admin.fiat.store'), [
            'code' => 'NGN',
            'name' => 'Nigerian Naira',
            'symbol' => '₦',
            'exchange_rate_to_usd' => 0.00062,
            'logo' => $svg,
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $fiat = FiatCurrency::where('code', 'NGN')->first();
        $this->assertNotNull($fiat->logo);
        $this->assertStringEndsWith('.svg', $fiat->logo);
    }

    public function test_uploaded_public_asset_is_served_even_without_the_storage_symlink(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('fiat-logos/test.svg', '<svg></svg>');

        // No public/storage symlink exists in this test environment at all
        // (Storage::fake() doesn't create one), proving the /storage route
        // itself — not the symlink — is what serves the file.
        $response = $this->get('/storage/fiat-logos/test.svg');

        $response->assertOk();
    }

    public function test_kyc_approval_creates_an_in_app_notification(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create(['kyc_status' => 'pending']);
        $doc = $user->kycDocuments()->create([
            'document_type' => 'id_card', 'file_path' => 'kyc/x.jpg', 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.kyc.approve', $doc));

        $this->assertSame(1, $user->notifications()->count());
        $this->assertSame('KYC Verified', $user->notifications()->first()->data['title']);
    }

    public function test_gift_card_rejection_notifies_the_user(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $giftCard = GiftCard::create([
            'user_id' => $user->id, 'card_type' => 'Amazon', 'card_number_hashed' => 'hash',
            'pin' => '1234', 'face_value' => 100, 'face_value_currency' => 'USD',
            'rate_applied' => 0.9, 'selling_price' => 90, 'payout_currency' => 'USD', 'status' => 'pending',
        ]);

        $this->actingAs($admin)->post(route('admin.giftcards.reject', $giftCard), ['reason' => 'Invalid PIN']);

        Notification::assertSentTo($user, AccountNotification::class);
    }

    public function test_notification_bell_shows_unread_count_and_can_mark_all_read(): void
    {
        $user = User::factory()->create();
        $user->notify(new AccountNotification('Test Notification', 'A test notification', 'info'));
        $user->notify(new AccountNotification('Test 2', 'Another one', 'success'));

        Livewire::actingAs($user)
            ->test(NotificationBell::class)
            ->assertSee('Test Notification')
            ->assertSee('2') // unread badge count
            ->call('markAllAsRead');

        $this->assertSame(0, $user->unreadNotifications()->count());
    }
}

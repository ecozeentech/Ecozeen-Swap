<?php

namespace Tests\Feature;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\ReferralCommission;
use App\Models\ReferralWithdrawal;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\User;
use App\Notifications\AccountNotification;
use App\Services\RateService;
use App\Services\ReferralService;
use App\Services\TradeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ReferralSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_user_gets_a_unique_referral_code_automatically(): void
    {
        $userA = User::factory()->create();
        $userB = User::factory()->create();

        $this->assertNotNull($userA->referral_code);
        $this->assertNotNull($userB->referral_code);
        $this->assertNotSame($userA->referral_code, $userB->referral_code);
    }

    public function test_visiting_register_with_a_valid_ref_code_shows_the_referrer_and_sets_referred_by(): void
    {
        $referrer = User::factory()->create();

        $response = $this->get(route('register', ['ref' => $referrer->referral_code]));
        $response->assertOk();
        $response->assertSee($referrer->username);

        $this->post(route('register'), [
            'name' => 'New User',
            'username' => 'newuser1',
            'email' => 'newuser1@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $newUser = User::where('email', 'newuser1@example.com')->first();
        $this->assertSame($referrer->id, $newUser->referred_by);
    }

    public function test_an_invalid_ref_code_is_silently_ignored(): void
    {
        $this->get(route('register', ['ref' => 'DOESNOTEXIST']))->assertOk();

        $this->post(route('register'), [
            'name' => 'New User',
            'username' => 'newuser2',
            'email' => 'newuser2@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $newUser = User::where('email', 'newuser2@example.com')->first();
        $this->assertNull($newUser->referred_by);
    }

    public function test_no_referral_tracking_happens_when_the_feature_is_disabled(): void
    {
        SystemSetting::set('referrals_enabled', false, 'boolean', 'features');
        $referrer = User::factory()->create();

        $this->get(route('register', ['ref' => $referrer->referral_code]));

        $this->post(route('register'), [
            'name' => 'New User',
            'username' => 'newuser3',
            'email' => 'newuser3@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'terms' => '1',
        ]);

        $newUser = User::where('email', 'newuser3@example.com')->first();
        $this->assertNull($newUser->referred_by);
    }

    protected function completeABuyFor(User $user, float $fiatAmount = 1000): Transaction
    {
        $admin = User::factory()->create();
        $btc = CryptoAsset::firstOrCreate(['symbol' => 'BTC'], ['name' => 'Bitcoin', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::firstOrCreate(['code' => 'USD'], ['name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        if (! app(RateService::class)->activeRate($btc->id, $usd->id)) {
            app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);
        }

        $transaction = app(TradeService::class)->initiateBuy($user, $btc, $usd, $fiatAmount, 'bank_transfer');

        return app(TradeService::class)->completeBuy($transaction, $admin);
    }

    public function test_referrer_earns_a_commission_when_the_referred_user_completes_a_buy(): void
    {
        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referred_by' => $referrer->id]);

        $this->completeABuyFor($referred, 1000);

        $this->assertSame(5.0, app(ReferralService::class)->totalEarned($referrer)); // 0.5% of 1000
        $this->assertSame(1, ReferralCommission::where('referrer_id', $referrer->id)->count());
    }

    public function test_no_commission_when_referrals_feature_is_disabled(): void
    {
        SystemSetting::set('referrals_enabled', false, 'boolean', 'features');
        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referred_by' => $referrer->id]);

        $this->completeABuyFor($referred, 1000);

        $this->assertSame(0.0, app(ReferralService::class)->totalEarned($referrer));
    }

    public function test_no_commission_for_a_user_with_no_referrer(): void
    {
        $user = User::factory()->create();

        $this->completeABuyFor($user, 1000);

        $this->assertSame(0, ReferralCommission::count());
    }

    public function test_signup_bonus_is_paid_once_on_the_first_completed_trade_when_enabled(): void
    {
        SystemSetting::set('referral_signup_bonus_enabled', true, 'boolean', 'referral');
        SystemSetting::set('referral_signup_bonus_amount', 2, 'string', 'referral');

        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referred_by' => $referrer->id]);

        $this->completeABuyFor($referred, 1000);

        $commissionTypes = ReferralCommission::where('referrer_id', $referrer->id)->pluck('type')->all();
        $this->assertContains('trade_commission', $commissionTypes);
        $this->assertContains('signup_bonus', $commissionTypes);
        $this->assertSame(7.0, app(ReferralService::class)->totalEarned($referrer)); // 5 + 2

        // A second trade should NOT pay the bonus again.
        $this->completeABuyFor($referred, 1000);
        $this->assertSame(1, ReferralCommission::where('referrer_id', $referrer->id)->where('type', 'signup_bonus')->count());
    }

    public function test_admin_can_change_the_commission_rate_and_min_withdrawal(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->put(route('admin.referrals.settings'), [
            'referral_commission_rate' => 2.5,
            'referral_min_withdrawal' => 50,
        ]);

        $response->assertRedirect();
        $this->assertSame('2.5', SystemSetting::get('referral_commission_rate'));
        $this->assertSame('50', SystemSetting::get('referral_min_withdrawal'));

        $referrer = User::factory()->create();
        $referred = User::factory()->create(['referred_by' => $referrer->id]);
        $this->completeABuyFor($referred, 1000);

        $this->assertSame(25.0, app(ReferralService::class)->totalEarned($referrer)); // 2.5% of 1000
    }

    public function test_withdrawal_is_rejected_below_the_admin_minimum(): void
    {
        SystemSetting::set('referral_min_withdrawal', 20, 'string', 'referral');
        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create(['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123', 'is_default' => true]);

        // Give them some balance, but below the minimum.
        ReferralCommission::create(['referrer_id' => $user->id, 'type' => 'manual_adjustment', 'amount' => 10]);

        $this->expectException(\RuntimeException::class);
        app(ReferralService::class)->requestWithdrawal($user, 10, $bankAccount);
    }

    public function test_user_cannot_withdraw_more_than_their_available_balance(): void
    {
        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create(['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123', 'is_default' => true]);
        ReferralCommission::create(['referrer_id' => $user->id, 'type' => 'manual_adjustment', 'amount' => 10]);

        $this->expectException(\RuntimeException::class);
        app(ReferralService::class)->requestWithdrawal($user, 100, $bankAccount);
    }

    public function test_full_withdrawal_lifecycle_via_http(): void
    {
        Notification::fake();

        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create(['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123', 'is_default' => true]);
        ReferralCommission::create(['referrer_id' => $user->id, 'type' => 'manual_adjustment', 'amount' => 100]);

        $response = $this->actingAs($user)->post(route('referrals.withdraw'), [
            'amount' => 50,
            'bank_account_id' => $bankAccount->id,
        ]);
        $response->assertRedirect(route('referrals.index'));

        $withdrawal = ReferralWithdrawal::first();
        $this->assertSame('pending', $withdrawal->status);
        $this->assertSame(50.0, app(ReferralService::class)->availableBalance($user)); // 100 - 50 reserved

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->actingAs($admin)->post(route('admin.referrals.withdrawals.approve', $withdrawal))->assertRedirect();
        $this->assertSame('approved', $withdrawal->fresh()->status);

        $this->actingAs($admin)->post(route('admin.referrals.withdrawals.paid', $withdrawal))->assertRedirect();
        $this->assertSame('paid', $withdrawal->fresh()->status);

        Notification::assertSentTo($user, AccountNotification::class);
    }

    public function test_rejecting_a_withdrawal_releases_the_reserved_balance(): void
    {
        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create(['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123', 'is_default' => true]);
        ReferralCommission::create(['referrer_id' => $user->id, 'type' => 'manual_adjustment', 'amount' => 100]);

        $withdrawal = app(ReferralService::class)->requestWithdrawal($user, 50, $bankAccount);
        $this->assertSame(50.0, app(ReferralService::class)->availableBalance($user));

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $this->actingAs($admin)->post(route('admin.referrals.withdrawals.reject', $withdrawal), ['reason' => 'Suspicious activity'])->assertRedirect();

        $this->assertSame('rejected', $withdrawal->fresh()->status);
        $this->assertSame(100.0, app(ReferralService::class)->availableBalance($user));
    }

    public function test_admin_can_reverse_a_commission(): void
    {
        $referrer = User::factory()->create();
        $commission = ReferralCommission::create(['referrer_id' => $referrer->id, 'type' => 'manual_adjustment', 'amount' => 50]);

        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $this->assertSame(50.0, app(ReferralService::class)->totalEarned($referrer));

        $this->actingAs($admin)->post(route('admin.referrals.reverse-commission', $commission))->assertRedirect();

        $this->assertTrue($commission->fresh()->is_reversed);
        $this->assertSame(0.0, app(ReferralService::class)->totalEarned($referrer));
    }

    public function test_referral_page_shows_stats_and_link(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('referrals.index'));

        $response->assertOk();
        $response->assertSee($user->referral_code);
        $response->assertSee($user->referralLink(), false);
    }

    public function test_referrals_route_shows_coming_soon_when_disabled(): void
    {
        SystemSetting::set('referrals_enabled', false, 'boolean', 'features');
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('referrals.index'))->assertSee('Coming Soon');
    }

    public function test_non_admin_cannot_access_admin_referral_pages(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('admin.referrals.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.referrals.withdrawals'))->assertForbidden();
    }

    public function test_user_can_cancel_their_own_pending_withdrawal(): void
    {
        $user = User::factory()->create();
        $bankAccount = $user->bankAccounts()->create(['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123', 'is_default' => true]);
        ReferralCommission::create(['referrer_id' => $user->id, 'type' => 'manual_adjustment', 'amount' => 100]);

        $withdrawal = app(ReferralService::class)->requestWithdrawal($user, 50, $bankAccount);

        $this->actingAs($user)->delete(route('referrals.withdraw.cancel', $withdrawal))->assertRedirect();

        $this->assertDatabaseMissing('referral_withdrawals', ['id' => $withdrawal->id]);
        $this->assertSame(100.0, app(ReferralService::class)->availableBalance($user));
    }

    public function test_user_cannot_cancel_someone_elses_withdrawal(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $bankAccount = $owner->bankAccounts()->create(['bank_name' => 'Test Bank', 'account_name' => 'Test', 'account_number' => '123', 'is_default' => true]);
        ReferralCommission::create(['referrer_id' => $owner->id, 'type' => 'manual_adjustment', 'amount' => 100]);

        $withdrawal = app(ReferralService::class)->requestWithdrawal($owner, 50, $bankAccount);

        $this->actingAs($intruder)->delete(route('referrals.withdraw.cancel', $withdrawal))->assertForbidden();
    }
}

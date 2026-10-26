<?php

namespace Tests\Feature;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\RateService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function completedTransaction(User $user, string $type, string $amount, string $currencyCode, ?float $usdEquivalent = null): Transaction
    {
        $wallet = Wallet::create(['user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => $currencyCode, 'balance' => 0, 'reserved_balance' => 0]);

        return Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => $type,
            'amount' => $amount,
            'currency_code' => $currencyCode,
            'status' => 'completed',
            'reference' => Transaction::generateReference(strtoupper($type)),
            'metadata' => $usdEquivalent !== null ? ['usd_equivalent' => $usdEquivalent] : null,
        ]);
    }

    public function test_dashboard_stat_cards_link_to_their_filtered_pages(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee(route('admin.users.index'), false);
        $response->assertSee(route('admin.transactions.index', ['status' => 'pending']), false);
        $response->assertSee(route('admin.giftcards.index', ['status' => 'pending']), false);
        $response->assertSee(route('admin.users.index', ['suspended' => 1]), false);
    }

    public function test_cumulative_volume_sums_different_currencies_correctly_as_usd(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();

        FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        FiatCurrency::create(['code' => 'NGN', 'name' => 'Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00067, 'is_active' => true]);

        // A buy stamped with its real usd_equivalent at creation time.
        $this->completedTransaction($user, 'buy', '0.01', 'BTC', 650.00);
        // A deposit with NO stored usd_equivalent — must fall back to a
        // live fiat conversion instead of being added to the total as if
        // "100" were already USD.
        $this->completedTransaction($user, 'deposit', '100', 'NGN');

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        // 650 (buy) + (100 * 0.00067 = 0.067, rounded to 0.07) deposit
        $response->assertSee('$650.07');
    }

    public function test_pending_transactions_do_not_count_toward_completed_volume(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);

        $wallet = Wallet::create(['user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD', 'balance' => 0, 'reserved_balance' => 0]);
        Transaction::create([
            'user_id' => $user->id, 'wallet_id' => $wallet->id, 'type' => 'deposit', 'amount' => '500',
            'currency_code' => 'USD', 'status' => 'pending', 'reference' => Transaction::generateReference('DEP'),
        ]);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertSee('$0.00');
        $response->assertDontSee('$500.00');
    }

    public function test_crypto_transaction_without_stored_usd_equivalent_falls_back_to_active_rate(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();

        $btc = CryptoAsset::create(['name' => 'Bitcoin', 'symbol' => 'BTC', 'decimal_places' => 8, 'is_active' => true]);
        $usd = FiatCurrency::create(['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1, 'is_active' => true]);
        app(RateService::class)->setRate($btc->id, $usd->id, 64000, 65000, 24, $admin);

        $wallet = Wallet::create(['user_id' => $user->id, 'currency_type' => 'crypto', 'currency_code' => 'BTC', 'balance' => 0, 'reserved_balance' => 0]);
        $transaction = Transaction::create([
            'user_id' => $user->id, 'wallet_id' => $wallet->id, 'type' => 'swap', 'amount' => '0.1',
            'currency_code' => 'BTC', 'status' => 'completed', 'reference' => Transaction::generateReference('SWAPIN'),
        ]);

        // 0.1 BTC * 64000 (buy_rate) = 6400
        $this->assertSame(6400.0, $transaction->usdEquivalent());
    }
}

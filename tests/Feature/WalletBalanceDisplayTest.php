<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletBalanceDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_shows_available_balance_not_raw_balance_when_funds_are_reserved(): void
    {
        $user = User::factory()->create();
        Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD',
            'balance' => '500.00', 'reserved_balance' => '200.00',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertOk();
        // Available (300) must be shown; the raw total (500) must not be
        // displayed as if it were spendable.
        $response->assertSee('300.00');
        $response->assertSee('200.00 held');
        $response->assertDontSee('+200.00');
        $response->assertDontSee('500.00');
    }

    public function test_wallet_index_shows_available_balance_as_the_primary_figure(): void
    {
        $user = User::factory()->create();
        Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'crypto', 'currency_code' => 'BTC',
            'balance' => '1.00000000', 'reserved_balance' => '0.25000000',
        ]);

        $response = $this->actingAs($user)->get(route('wallet.index'));

        $response->assertOk();
        $response->assertSee('0.750000');
        $response->assertSee('held and not available');
        $response->assertDontSee('1.000000 total');
    }

    public function test_wallet_index_shows_plain_available_label_when_nothing_is_reserved(): void
    {
        $user = User::factory()->create();
        Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD',
            'balance' => '100.00', 'reserved_balance' => '0.00',
        ]);

        $response = $this->actingAs($user)->get(route('wallet.index'));

        $response->assertOk();
        $response->assertSee('Available balance');
    }

    public function test_admin_user_page_shows_available_and_reserved_separately(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        Wallet::create([
            'user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD',
            'balance' => '1000.00', 'reserved_balance' => '400.00',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.users.show', $user));

        $response->assertOk();
        $response->assertSee('600.000000');
        $response->assertSee('400.000000 held, not available');
    }
}

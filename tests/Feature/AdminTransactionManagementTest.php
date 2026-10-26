<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTransactionManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function pendingWithdrawal(User $user, string $amount = '100'): Transaction
    {
        $wallet = Wallet::create([
            'user_id' => $user->id,
            'currency_type' => 'fiat',
            'currency_code' => 'USD',
            'balance' => '500',
            'reserved_balance' => $amount,
        ]);

        return Transaction::create([
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'type' => 'withdrawal',
            'amount' => $amount,
            'currency_code' => 'USD',
            'status' => 'pending',
            'reference' => Transaction::generateReference('WD'),
        ]);
    }

    public function test_admin_can_approve_a_pending_withdrawal(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $transaction = $this->pendingWithdrawal($user, '100');

        $response = $this->actingAs($admin)->post(route('admin.transactions.approve-withdrawal', $transaction));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        $transaction->refresh();
        $this->assertSame('completed', $transaction->status);

        $wallet = $transaction->wallet;
        $this->assertEquals('400.00000000', $wallet->balance); // 500 - 100
        $this->assertEquals('0.00000000', $wallet->reserved_balance);
    }

    public function test_approving_a_non_withdrawal_transaction_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $wallet = Wallet::create(['user_id' => $user->id, 'currency_type' => 'fiat', 'currency_code' => 'USD', 'balance' => 0, 'reserved_balance' => 0]);
        $transaction = Transaction::create([
            'user_id' => $user->id, 'wallet_id' => $wallet->id, 'type' => 'deposit', 'amount' => 50,
            'currency_code' => 'USD', 'status' => 'pending', 'reference' => Transaction::generateReference('DEP'),
        ]);

        $response = $this->actingAs($admin)->post(route('admin.transactions.approve-withdrawal', $transaction));

        $response->assertSessionHasErrors('transaction');
        $this->assertSame('pending', $transaction->fresh()->status);
    }

    public function test_approving_an_already_processed_withdrawal_is_rejected(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $transaction = $this->pendingWithdrawal($user, '100');
        $transaction->update(['status' => 'completed']);

        $response = $this->actingAs($admin)->post(route('admin.transactions.approve-withdrawal', $transaction));

        $response->assertSessionHasErrors('transaction');
    }

    public function test_rejecting_a_withdrawal_releases_the_hold_without_touching_balance(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $transaction = $this->pendingWithdrawal($user, '100');

        $this->actingAs($admin)->post(route('admin.transactions.reject', $transaction), ['reason' => 'Suspicious']);

        $transaction->refresh();
        $this->assertSame('failed', $transaction->status);
        $wallet = $transaction->wallet->fresh();
        $this->assertEquals('500.00000000', $wallet->balance);
        $this->assertEquals('0.00000000', $wallet->reserved_balance);
    }

    public function test_mark_paid_is_rejected_for_withdrawal_type(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $transaction = $this->pendingWithdrawal($user, '100');

        $response = $this->actingAs($admin)->post(route('admin.transactions.mark-paid', $transaction));

        $response->assertSessionHasErrors('transaction');
        $this->assertSame('pending', $transaction->fresh()->status);
    }

    public function test_confirm_sell_is_rejected_for_non_sell_type(): void
    {
        $admin = User::factory()->create();
        $admin->assignRole('super-admin');
        $user = User::factory()->create();
        $transaction = $this->pendingWithdrawal($user, '100');

        $response = $this->actingAs($admin)->post(route('admin.transactions.confirm-sell', $transaction));

        $response->assertSessionHasErrors('transaction');
    }
}

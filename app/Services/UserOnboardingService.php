<?php

namespace App\Services;

use App\Models\CryptoAsset;
use App\Models\FiatCurrency;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UserOnboardingService
{
    public function __construct(protected WalletService $wallets) {}

    /**
     * Provision a zero-balance wallet for every active fiat and crypto
     * asset so the wallet dashboard has something to render immediately
     * after registration.
     */
    public function provisionWallets(User $user): void
    {
        DB::transaction(function () use ($user) {
            foreach (FiatCurrency::query()->active()->get() as $fiat) {
                $this->wallets->getOrCreateWallet($user, 'fiat', $fiat->code);
            }

            foreach (CryptoAsset::query()->active()->get() as $crypto) {
                $this->wallets->getOrCreateWallet($user, 'crypto', $crypto->symbol);
            }
        });
    }
}

<?php

namespace Database\Seeders;

use App\Models\CryptoAsset;
use App\Models\CryptoWallet;
use Illuminate\Database\Seeder;

class CryptoWalletSeeder extends Seeder
{
    public function run(): void
    {
        $addresses = [
            'BTC' => 'bc1qxy2kgdygjrsqtzq2n0yrf2493p83kkfjhx0wlh',
            'ETH' => '0x71C7656EC7ab88b098defB751B7401B5f6d8976',
            'USDT' => 'TXYZopYRdj2D9XRtbG411XZZ3kM5VkAeBf',
            'BNB' => 'bnb1grpf0955h0ykzq3ar5nmum7y6gdfl6lxfn46h2',
        ];

        foreach ($addresses as $symbol => $address) {
            $asset = CryptoAsset::query()->where('symbol', $symbol)->first();

            if (! $asset) {
                continue;
            }

            CryptoWallet::query()->firstOrCreate(
                ['crypto_asset_id' => $asset->id, 'wallet_address' => $address],
                ['label' => 'Hot Wallet', 'is_active' => true, 'is_default' => true]
            );
        }
    }
}

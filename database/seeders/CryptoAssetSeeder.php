<?php

namespace Database\Seeders;

use App\Models\CryptoAsset;
use Illuminate\Database\Seeder;

class CryptoAssetSeeder extends Seeder
{
    public function run(): void
    {
        $assets = [
            ['name' => 'Bitcoin', 'symbol' => 'BTC', 'network' => 'Bitcoin', 'decimal_places' => 8],
            ['name' => 'Ethereum', 'symbol' => 'ETH', 'network' => 'ERC20', 'decimal_places' => 8],
            ['name' => 'Tether USD', 'symbol' => 'USDT', 'network' => 'TRC20', 'decimal_places' => 6],
            ['name' => 'BNB', 'symbol' => 'BNB', 'network' => 'BEP20', 'decimal_places' => 8],
        ];

        foreach ($assets as $asset) {
            CryptoAsset::query()->updateOrCreate(
                ['symbol' => $asset['symbol']],
                array_merge($asset, ['is_active' => true])
            );
        }
    }
}

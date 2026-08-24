<?php

namespace Database\Seeders;

use App\Models\FiatCurrency;
use Illuminate\Database\Seeder;

class FiatCurrencySeeder extends Seeder
{
    public function run(): void
    {
        $currencies = [
            ['code' => 'NGN', 'name' => 'Nigerian Naira', 'symbol' => '₦', 'exchange_rate_to_usd' => 0.00062],
            ['code' => 'GHS', 'name' => 'Ghanaian Cedi', 'symbol' => 'GH₵', 'exchange_rate_to_usd' => 0.068],
            ['code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'exchange_rate_to_usd' => 1],
        ];

        foreach ($currencies as $currency) {
            FiatCurrency::query()->updateOrCreate(
                ['code' => $currency['code']],
                array_merge($currency, ['is_active' => true])
            );
        }
    }
}

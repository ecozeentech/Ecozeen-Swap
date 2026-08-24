<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            FiatCurrencySeeder::class,
            CryptoAssetSeeder::class,
            PaymentGatewaySeeder::class,
            SystemSettingSeeder::class,
            AdminUserSeeder::class,
            DailyRateSeeder::class,
            GiftCardProductSeeder::class,
            PageSeeder::class,
            BlogSeeder::class,
        ]);
    }
}

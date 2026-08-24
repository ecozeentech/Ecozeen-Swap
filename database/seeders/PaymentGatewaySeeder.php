<?php

namespace Database\Seeders;

use App\Models\PaymentGateway;
use Illuminate\Database\Seeder;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        $gateways = [
            [
                'name' => 'Paystack',
                'slug' => 'paystack',
                'is_active' => false,
                'credentials' => ['public_key' => '', 'secret_key' => ''],
                'metadata' => [],
            ],
            [
                'name' => 'Flutterwave',
                'slug' => 'flutterwave',
                'is_active' => false,
                'credentials' => ['public_key' => '', 'secret_key' => '', 'secret_hash' => ''],
                'metadata' => [],
            ],
            [
                'name' => 'Bank Transfer',
                'slug' => 'bank_transfer',
                'is_active' => true,
                'credentials' => [],
                'metadata' => [
                    'bank_name' => 'Guaranty Trust Bank',
                    'account_number' => '0123456789',
                    'account_name' => 'Ecozeen Tech Ltd',
                ],
            ],
        ];

        foreach ($gateways as $gateway) {
            PaymentGateway::query()->updateOrCreate(['slug' => $gateway['slug']], $gateway);
        }
    }
}

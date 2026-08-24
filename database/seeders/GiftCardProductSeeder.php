<?php

namespace Database\Seeders;

use App\Models\GiftCardProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GiftCardProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['name' => 'Amazon Gift Card', 'currency' => 'USD', 'countries' => null],
            ['name' => 'iTunes Gift Card', 'currency' => 'USD', 'countries' => null],
            ['name' => 'Google Play Gift Card', 'currency' => 'USD', 'countries' => null],
            ['name' => 'Steam Wallet Card', 'currency' => 'USD', 'countries' => null],
            ['name' => 'Walmart Gift Card', 'currency' => 'USD', 'countries' => ['US']],
            ['name' => 'Razer Gold Gift Card', 'currency' => 'USD', 'countries' => null],
        ];

        foreach ($products as $product) {
            GiftCardProduct::query()->firstOrCreate(
                ['name' => $product['name']],
                array_merge($product, [
                    'slug' => Str::slug($product['name']).'-'.Str::random(4),
                    'min_amount' => 5,
                    'max_amount' => 2000,
                    'is_active' => true,
                ])
            );
        }
    }
}

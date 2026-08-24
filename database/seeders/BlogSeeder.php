<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Seeder;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $category = BlogCategory::query()->firstOrCreate(
            ['slug' => 'market-insights'],
            ['name' => 'Market Insights', 'description' => 'Crypto market notes and price commentary.']
        );

        $guides = BlogCategory::query()->firstOrCreate(
            ['slug' => 'guides'],
            ['name' => 'Guides', 'description' => 'How-to guides for using Ecozeen Swap.']
        );

        $admin = User::query()->where('email', 'admin@ecozeenswap.com')->first();

        $posts = [
            [
                'blog_category_id' => $guides->id,
                'title' => 'How to Buy Your First Crypto on Ecozeen Swap',
                'excerpt' => 'A step-by-step walkthrough of buying Bitcoin, Ethereum, or USDT directly from Ecozeen Swap.',
                'content' => '<p>Buying crypto on Ecozeen Swap takes just a few minutes. Here is how it works.</p><h2>Step 1: Create an account</h2><p>Sign up with a username, email, and password, then verify your email address.</p><h2>Step 2: Fund your wallet</h2><p>Fund your fiat wallet via Paystack, Flutterwave, or bank transfer.</p><h2>Step 3: Buy at the published rate</h2><p>Head to the Buy page, choose your crypto and fiat currency, enter an amount, and confirm. Your crypto is credited once payment is confirmed.</p>',
            ],
            [
                'blog_category_id' => $category->id,
                'title' => 'Why Ecozeen Swap Uses a Single-Vendor Model',
                'excerpt' => 'No P2P ads, no waiting on strangers — just transparent rates and fast settlement.',
                'content' => '<p>Many crypto platforms rely on peer-to-peer marketplaces, where you trade with anonymous third parties. Ecozeen Swap takes a different approach.</p><h2>You always trade with us</h2><p>Every buy, sell, and swap on Ecozeen Swap settles directly with the platform. There are no ads, no listings, and no negotiating with strangers.</p><h2>Transparent daily rates</h2><p>Our rates refresh every 24 hours and are visible on your dashboard with a live countdown, so you always know exactly what you are trading at.</p>',
            ],
        ];

        foreach ($posts as $post) {
            BlogPost::query()->firstOrCreate(
                ['title' => $post['title']],
                array_merge($post, [
                    'author_id' => $admin?->id,
                    'status' => 'published',
                    'published_at' => now()->subDays(2),
                ])
            );
        }
    }
}

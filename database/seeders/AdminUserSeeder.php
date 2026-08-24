<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\UserOnboardingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@ecozeenswap.com'],
            [
                'name' => 'Ecozeen Swap Admin',
                'username' => 'ecozeenadmin',
                'phone' => '+2348000000000',
                'password' => Hash::make(env('ADMIN_DEFAULT_PASSWORD', 'ChangeMe123!')),
                'email_verified_at' => now(),
                'kyc_status' => 'verified',
                'daily_trade_limit' => 999999999,
            ]
        );

        $admin->syncRoles(['super-admin']);

        app(UserOnboardingService::class)->provisionWallets($admin);
    }
}

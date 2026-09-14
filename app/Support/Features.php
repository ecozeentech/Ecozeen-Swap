<?php

namespace App\Support;

use App\Models\SystemSetting;

/**
 * Central registry of the platform's togglable features.
 *
 * Admins can enable/disable any of these from the admin panel. When a
 * feature is disabled its frontend page renders a "Coming Soon" notice
 * instead of the real functionality.
 */
class Features
{
    public const BUY = 'buy_enabled';

    public const SELL = 'sell_enabled';

    public const SWAP = 'swap_enabled';

    public const GIFTCARDS = 'giftcard_enabled';

    public const INVOICING = 'invoicing_enabled';

    public const WITHDRAWALS = 'withdrawals_enabled';

    public const DEPOSITS = 'deposits_enabled';

    public const REGISTRATION = 'registration_enabled';

    public const REFERRALS = 'referrals_enabled';

    /**
     * @return array<string, string>
     */
    public static function all(): array
    {
        return [
            self::BUY => 'Buy Crypto',
            self::SELL => 'Sell Crypto',
            self::SWAP => 'Swap Crypto',
            self::GIFTCARDS => 'Gift Cards',
            self::INVOICING => 'Invoicing',
            self::WITHDRAWALS => 'Withdrawals',
            self::DEPOSITS => 'Deposits',
            self::REGISTRATION => 'New Registrations',
            self::REFERRALS => 'Referral Program',
        ];
    }

    public static function isEnabled(string $key): bool
    {
        return (bool) SystemSetting::get($key, true);
    }

    public static function comingSoonMessage(): string
    {
        return (string) SystemSetting::get('coming_soon_message', 'This feature is coming soon. Please check back shortly.');
    }
}

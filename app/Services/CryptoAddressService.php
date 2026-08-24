<?php

namespace App\Services;

use App\Models\CryptoAsset;
use App\Models\CryptoWalletAddress;
use App\Models\User;

/**
 * Generates and stores a stable deposit address per user/asset.
 *
 * NOTE: This does not integrate with a real blockchain node. In a
 * production deployment, replace generateAddress() with a call to your
 * custody provider / node's address-generation API (e.g. BitGo,
 * Fireblocks, or a self-hosted HD wallet service).
 */
class CryptoAddressService
{
    public function addressFor(User $user, CryptoAsset $asset): CryptoWalletAddress
    {
        return CryptoWalletAddress::query()->firstOrCreate(
            ['user_id' => $user->id, 'crypto_asset_id' => $asset->id],
            ['address' => $this->generateAddress($asset), 'memo_tag' => $this->generateMemoTag($asset, $user)]
        );
    }

    protected function generateAddress(CryptoAsset $asset): string
    {
        $prefix = match (true) {
            str_contains(strtoupper($asset->network ?? ''), 'TRC20') => 'T',
            str_contains(strtoupper($asset->network ?? ''), 'ERC20') || strtoupper($asset->symbol) === 'ETH' => '0x',
            strtoupper($asset->symbol) === 'BTC' => 'bc1q',
            default => strtolower($asset->symbol).'_',
        };

        return $prefix.substr(bin2hex(random_bytes(20)), 0, 34);
    }

    protected function generateMemoTag(CryptoAsset $asset, User $user): ?string
    {
        if (in_array(strtoupper($asset->symbol), ['XRP', 'XLM'])) {
            return (string) random_int(100000000, 999999999);
        }

        return null;
    }
}

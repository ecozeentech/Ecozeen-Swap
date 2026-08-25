<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Models\CryptoWallet;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CryptoWalletController extends Controller
{
    public function index(): View
    {
        return view('admin.crypto-wallets.index', [
            'cryptoAssets' => CryptoAsset::query()->orderBy('name')->get(),
            'wallets' => CryptoWallet::query()->with('cryptoAsset')->orderBy('crypto_asset_id')->orderByDesc('is_default')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        $wallet = CryptoWallet::create($data);

        if ($data['is_default']) {
            $this->makeDefault($wallet);
        }

        ActivityLog::record(auth()->id(), 'admin_added_crypto_wallet', ['crypto_asset_id' => $wallet->crypto_asset_id, 'label' => $wallet->label]);

        return back()->with('status', 'crypto-wallet-added');
    }

    public function update(Request $request, CryptoWallet $cryptoWallet): RedirectResponse
    {
        $data = $this->validated($request);
        $cryptoWallet->update($data);

        if ($data['is_default']) {
            $this->makeDefault($cryptoWallet);
        }

        ActivityLog::record(auth()->id(), 'admin_updated_crypto_wallet', ['id' => $cryptoWallet->id]);

        return back()->with('status', 'crypto-wallet-updated');
    }

    public function toggleActive(CryptoWallet $cryptoWallet): RedirectResponse
    {
        $cryptoWallet->update(['is_active' => ! $cryptoWallet->is_active]);

        return back()->with('status', $cryptoWallet->is_active ? 'crypto-wallet-activated' : 'crypto-wallet-deactivated');
    }

    public function setDefault(CryptoWallet $cryptoWallet): RedirectResponse
    {
        $this->makeDefault($cryptoWallet);

        return back()->with('status', 'crypto-wallet-default-set');
    }

    public function destroy(CryptoWallet $cryptoWallet): RedirectResponse
    {
        ActivityLog::record(auth()->id(), 'admin_deleted_crypto_wallet', ['id' => $cryptoWallet->id]);

        $cryptoWallet->delete();

        return back()->with('status', 'crypto-wallet-deleted');
    }

    protected function makeDefault(CryptoWallet $wallet): void
    {
        CryptoWallet::query()
            ->where('crypto_asset_id', $wallet->crypto_asset_id)
            ->where('id', '!=', $wallet->id)
            ->update(['is_default' => false]);

        $wallet->update(['is_default' => true]);
    }

    protected function validated(Request $request): array
    {
        $validated = $request->validate([
            'crypto_asset_id' => ['required', 'exists:crypto_assets,id'],
            'wallet_address' => ['required', 'string', 'max:255'],
            'label' => ['nullable', 'string', 'max:100'],
            'memo_tag' => ['nullable', 'string', 'max:100'],
            'is_active' => ['nullable', 'boolean'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['is_default'] = $request->boolean('is_default');

        return $validated;
    }
}

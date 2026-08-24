<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Models\CryptoWalletAddress;
use App\Models\DailyRate;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CryptoAssetController extends Controller
{
    public function __construct(protected MediaUploadService $media) {}

    public function index(): View
    {
        return view('admin.crypto.index', ['assets' => CryptoAsset::query()->latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:15', 'unique:crypto_assets,symbol'],
            'network' => ['nullable', 'string', 'max:100'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:18'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $logoPath = $request->hasFile('logo') ? $this->media->store($request->file('logo'), 'crypto-logos') : null;

        $asset = CryptoAsset::create([
            'name' => $request->input('name'),
            'symbol' => strtoupper($request->input('symbol')),
            'network' => $request->input('network'),
            'decimal_places' => $request->input('decimal_places'),
            'logo' => $logoPath,
            'is_active' => true,
        ]);

        ActivityLog::record(auth()->id(), 'admin_created_crypto_asset', ['symbol' => $asset->symbol]);

        return back()->with('status', 'crypto-created');
    }

    public function update(Request $request, CryptoAsset $cryptoAsset): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:15', Rule::unique('crypto_assets', 'symbol')->ignore($cryptoAsset->id)],
            'network' => ['nullable', 'string', 'max:100'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:18'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only(['name', 'network', 'decimal_places']);
        $data['symbol'] = strtoupper($request->input('symbol'));
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->media->replace($request->file('logo'), 'crypto-logos', $cryptoAsset->logo);
        }

        $cryptoAsset->update($data);

        ActivityLog::record(auth()->id(), 'admin_updated_crypto_asset', ['symbol' => $cryptoAsset->symbol]);

        return back()->with('status', 'crypto-updated');
    }

    public function toggleActive(CryptoAsset $cryptoAsset): RedirectResponse
    {
        $cryptoAsset->update(['is_active' => ! $cryptoAsset->is_active]);

        ActivityLog::record(auth()->id(), $cryptoAsset->is_active ? 'admin_activated_crypto_asset' : 'admin_deactivated_crypto_asset', ['symbol' => $cryptoAsset->symbol]);

        return back()->with('status', $cryptoAsset->is_active ? 'crypto-activated' : 'crypto-deactivated');
    }

    public function destroy(CryptoAsset $cryptoAsset): RedirectResponse
    {
        $inUse = Wallet::query()->where('currency_code', $cryptoAsset->symbol)->where('balance', '>', 0)->exists()
            || Transaction::query()->where('currency_code', $cryptoAsset->symbol)->exists()
            || DailyRate::query()->where('crypto_asset_id', $cryptoAsset->id)->exists()
            || CryptoWalletAddress::query()->where('crypto_asset_id', $cryptoAsset->id)->exists();

        if ($inUse) {
            return back()->withErrors(['crypto' => "{$cryptoAsset->symbol} has existing wallets, transactions, or rate history and can't be deleted. Deactivate it instead."]);
        }

        $this->media->forget($cryptoAsset->logo);
        $symbol = $cryptoAsset->symbol;
        $cryptoAsset->delete();

        ActivityLog::record(auth()->id(), 'admin_deleted_crypto_asset', ['symbol' => $symbol]);

        return back()->with('status', 'crypto-deleted');
    }
}

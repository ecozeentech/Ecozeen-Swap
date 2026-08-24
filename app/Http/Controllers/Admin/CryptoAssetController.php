<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CryptoAssetController extends Controller
{
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

        $logoPath = $request->hasFile('logo') ? $request->file('logo')->store('crypto-logos', 'public') : null;

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
            'network' => ['nullable', 'string', 'max:100'],
            'decimal_places' => ['required', 'integer', 'min:0', 'max:18'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
        ]);

        $data = $request->only(['name', 'network', 'decimal_places']);
        $data['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('crypto-logos', 'public');
        }

        $cryptoAsset->update($data);

        ActivityLog::record(auth()->id(), 'admin_updated_crypto_asset', ['symbol' => $cryptoAsset->symbol]);

        return back()->with('status', 'crypto-updated');
    }

    public function destroy(CryptoAsset $cryptoAsset): RedirectResponse
    {
        $cryptoAsset->update(['is_active' => false]);

        ActivityLog::record(auth()->id(), 'admin_deactivated_crypto_asset', ['symbol' => $cryptoAsset->symbol]);

        return back()->with('status', 'crypto-deactivated');
    }
}

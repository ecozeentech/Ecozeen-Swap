<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DailyRate;
use App\Services\RateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DailyRateController extends Controller
{
    public function __construct(protected RateService $rates) {}

    public function index(): View
    {
        return view('admin.rates.index', [
            'cryptoAssets' => $this->rates->cryptoAssets(),
            'fiatCurrencies' => $this->rates->fiatCurrencies(),
            'activeRates' => $this->rates->allActiveRates(),
            'history' => DailyRate::query()->with(['cryptoAsset', 'fiatCurrency', 'createdBy'])->latest()->limit(30)->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'crypto_asset_id' => ['required', 'exists:crypto_assets,id'],
            'fiat_currency_id' => ['required', 'exists:fiat_currencies,id'],
            'buy_rate' => ['required', 'numeric', 'min:0'],
            'sell_rate' => ['required', 'numeric', 'min:0', 'gte:buy_rate'],
            'hours_valid' => ['nullable', 'integer', 'min:1', 'max:168'],
        ]);

        $rate = $this->rates->setRate(
            $request->input('crypto_asset_id'),
            $request->input('fiat_currency_id'),
            $request->input('buy_rate'),
            $request->input('sell_rate'),
            $request->input('hours_valid', 24),
            $request->user()
        );

        ActivityLog::record(auth()->id(), 'admin_set_daily_rate', [
            'crypto_asset_id' => $rate->crypto_asset_id,
            'fiat_currency_id' => $rate->fiat_currency_id,
            'buy_rate' => (string) $rate->buy_rate,
            'sell_rate' => (string) $rate->sell_rate,
        ]);

        return back()->with('status', 'rate-set');
    }

    public function update(Request $request, DailyRate $dailyRate): RedirectResponse
    {
        $request->validate([
            'crypto_asset_id' => ['required', 'exists:crypto_assets,id'],
            'fiat_currency_id' => ['required', 'exists:fiat_currencies,id'],
            'buy_rate' => ['required', 'numeric', 'min:0'],
            'sell_rate' => ['required', 'numeric', 'min:0', 'gte:buy_rate'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $newCryptoAssetId = (int) $request->input('crypto_asset_id');
        $newFiatCurrencyId = (int) $request->input('fiat_currency_id');
        $pairChanged = $newCryptoAssetId !== $dailyRate->crypto_asset_id || $newFiatCurrencyId !== $dailyRate->fiat_currency_id;

        // If the admin re-pointed this rate at a different pair and that
        // pair already has its own active rate, deactivate that one first
        // so there's never more than one active rate per pair.
        if ($pairChanged && $request->boolean('is_active')) {
            DailyRate::query()
                ->where('crypto_asset_id', $newCryptoAssetId)
                ->where('fiat_currency_id', $newFiatCurrencyId)
                ->where('id', '!=', $dailyRate->id)
                ->where('is_active', true)
                ->update(['is_active' => false]);
        }

        $dailyRate->update([
            'crypto_asset_id' => $newCryptoAssetId,
            'fiat_currency_id' => $newFiatCurrencyId,
            'buy_rate' => $request->input('buy_rate'),
            'sell_rate' => $request->input('sell_rate'),
            'expires_at' => $request->input('expires_at') ?: $dailyRate->expires_at,
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record(auth()->id(), 'admin_overrode_daily_rate', [
            'rate_id' => $dailyRate->id,
            'pair_changed' => $pairChanged,
        ]);

        return back()->with('status', 'rate-updated');
    }

    public function toggleActive(DailyRate $dailyRate): RedirectResponse
    {
        // Flipping is_active on an already-expired rate would leave it
        // matching is_active=true but still invisible everywhere (the
        // "active" scope also requires expires_at to be in the future) —
        // renew() is the correct way to bring an expired rate back.
        if ($dailyRate->isExpired() && ! $dailyRate->is_active) {
            return back()->withErrors(['rate' => 'This rate has expired — use "Renew" to reactivate it with a fresh expiry window instead.']);
        }

        $dailyRate->update(['is_active' => ! $dailyRate->is_active]);

        ActivityLog::record(auth()->id(), $dailyRate->is_active ? 'admin_activated_daily_rate' : 'admin_deactivated_daily_rate', ['rate_id' => $dailyRate->id]);

        return back()->with('status', $dailyRate->is_active ? 'rate-activated' : 'rate-deactivated');
    }

    public function renew(Request $request, DailyRate $dailyRate): RedirectResponse
    {
        $request->validate([
            'hours_valid' => ['nullable', 'integer', 'min:1', 'max:168'],
            'buy_rate' => ['nullable', 'numeric', 'min:0'],
            'sell_rate' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($request->filled('buy_rate') && $request->filled('sell_rate') && (float) $request->input('sell_rate') < (float) $request->input('buy_rate')) {
            return back()->withErrors(['sell_rate' => 'Sell rate must be greater than or equal to the buy rate.']);
        }

        $this->rates->renewRate(
            $dailyRate,
            $request->input('hours_valid') ? (int) $request->input('hours_valid') : null,
            $request->filled('buy_rate') ? (float) $request->input('buy_rate') : null,
            $request->filled('sell_rate') ? (float) $request->input('sell_rate') : null,
        );

        ActivityLog::record(auth()->id(), 'admin_renewed_daily_rate', [
            'rate_id' => $dailyRate->id,
            'crypto_asset_id' => $dailyRate->crypto_asset_id,
            'fiat_currency_id' => $dailyRate->fiat_currency_id,
        ]);

        return back()->with('status', 'rate-renewed');
    }

    public function destroy(DailyRate $dailyRate): RedirectResponse
    {
        ActivityLog::record(auth()->id(), 'admin_deleted_daily_rate', [
            'rate_id' => $dailyRate->id,
            'crypto_asset_id' => $dailyRate->crypto_asset_id,
            'fiat_currency_id' => $dailyRate->fiat_currency_id,
        ]);

        $dailyRate->delete();

        return back()->with('status', 'rate-deleted');
    }
}

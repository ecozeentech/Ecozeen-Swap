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
            'buy_rate' => ['required', 'numeric', 'min:0'],
            'sell_rate' => ['required', 'numeric', 'min:0', 'gte:buy_rate'],
            'expires_at' => ['nullable', 'date'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $dailyRate->update([
            'buy_rate' => $request->input('buy_rate'),
            'sell_rate' => $request->input('sell_rate'),
            'expires_at' => $request->input('expires_at') ?: $dailyRate->expires_at,
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record(auth()->id(), 'admin_overrode_daily_rate', ['rate_id' => $dailyRate->id]);

        return back()->with('status', 'rate-updated');
    }

    public function toggleActive(DailyRate $dailyRate): RedirectResponse
    {
        $dailyRate->update(['is_active' => ! $dailyRate->is_active]);

        ActivityLog::record(auth()->id(), $dailyRate->is_active ? 'admin_activated_daily_rate' : 'admin_deactivated_daily_rate', ['rate_id' => $dailyRate->id]);

        return back()->with('status', $dailyRate->is_active ? 'rate-activated' : 'rate-deactivated');
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

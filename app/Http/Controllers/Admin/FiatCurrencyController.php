<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\DailyRate;
use App\Models\FiatCurrency;
use App\Models\Transaction;
use App\Models\Wallet;
use App\Services\MediaUploadService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FiatCurrencyController extends Controller
{
    public function __construct(protected MediaUploadService $media) {}

    public function index(): View
    {
        return view('admin.fiat.index', ['currencies' => FiatCurrency::query()->latest()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'code' => ['required', 'string', 'max:6', 'unique:fiat_currencies,code'],
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:5'],
            'exchange_rate_to_usd' => ['required', 'numeric', 'min:0'],
            'logo' => MediaUploadService::logoRules(),
        ]);

        $logoPath = $request->hasFile('logo') ? $this->media->store($request->file('logo'), 'fiat-logos') : null;

        $currency = FiatCurrency::create([
            'code' => strtoupper($request->input('code')),
            'name' => $request->input('name'),
            'symbol' => $request->input('symbol'),
            'logo' => $logoPath,
            'exchange_rate_to_usd' => $request->input('exchange_rate_to_usd'),
            'is_active' => true,
        ]);

        ActivityLog::record(auth()->id(), 'admin_created_fiat_currency', ['code' => $currency->code]);

        return back()->with('status', 'fiat-created');
    }

    public function update(Request $request, FiatCurrency $fiatCurrency): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'symbol' => ['required', 'string', 'max:5'],
            'exchange_rate_to_usd' => ['required', 'numeric', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'logo' => MediaUploadService::logoRules(),
        ]);

        $data = [
            'name' => $request->input('name'),
            'symbol' => $request->input('symbol'),
            'exchange_rate_to_usd' => $request->input('exchange_rate_to_usd'),
            'is_active' => $request->boolean('is_active'),
        ];

        if ($request->hasFile('logo')) {
            $data['logo'] = $this->media->replace($request->file('logo'), 'fiat-logos', $fiatCurrency->logo);
        }

        $fiatCurrency->update($data);

        ActivityLog::record(auth()->id(), 'admin_updated_fiat_currency', ['code' => $fiatCurrency->code]);

        return back()->with('status', 'fiat-updated');
    }

    public function toggleActive(FiatCurrency $fiatCurrency): RedirectResponse
    {
        $fiatCurrency->update(['is_active' => ! $fiatCurrency->is_active]);

        ActivityLog::record(auth()->id(), $fiatCurrency->is_active ? 'admin_activated_fiat_currency' : 'admin_deactivated_fiat_currency', ['code' => $fiatCurrency->code]);

        return back()->with('status', $fiatCurrency->is_active ? 'fiat-activated' : 'fiat-deactivated');
    }

    public function destroy(FiatCurrency $fiatCurrency): RedirectResponse
    {
        $inUse = Wallet::query()->where('currency_code', $fiatCurrency->code)->where('balance', '>', 0)->exists()
            || Transaction::query()->where('currency_code', $fiatCurrency->code)->exists()
            || DailyRate::query()->where('fiat_currency_id', $fiatCurrency->id)->exists();

        if ($inUse) {
            return back()->withErrors(['fiat' => "{$fiatCurrency->code} has existing wallets, transactions, or rate history and can't be deleted. Deactivate it instead."]);
        }

        $this->media->forget($fiatCurrency->logo);
        $code = $fiatCurrency->code;
        $fiatCurrency->delete();

        ActivityLog::record(auth()->id(), 'admin_deleted_fiat_currency', ['code' => $code]);

        return back()->with('status', 'fiat-deleted');
    }
}

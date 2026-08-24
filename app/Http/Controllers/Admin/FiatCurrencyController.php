<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\FiatCurrency;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FiatCurrencyController extends Controller
{
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
        ]);

        $currency = FiatCurrency::create([
            'code' => strtoupper($request->input('code')),
            'name' => $request->input('name'),
            'symbol' => $request->input('symbol'),
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
        ]);

        $fiatCurrency->update([
            'name' => $request->input('name'),
            'symbol' => $request->input('symbol'),
            'exchange_rate_to_usd' => $request->input('exchange_rate_to_usd'),
            'is_active' => $request->boolean('is_active'),
        ]);

        ActivityLog::record(auth()->id(), 'admin_updated_fiat_currency', ['code' => $fiatCurrency->code]);

        return back()->with('status', 'fiat-updated');
    }
}

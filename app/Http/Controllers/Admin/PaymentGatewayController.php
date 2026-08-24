<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\PaymentGateway;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PaymentGatewayController extends Controller
{
    public function index(): View
    {
        return view('admin.gateways.index', ['gateways' => PaymentGateway::query()->orderBy('name')->get()]);
    }

    public function update(Request $request, PaymentGateway $paymentGateway): RedirectResponse
    {
        $request->validate([
            'is_active' => ['nullable', 'boolean'],
            'public_key' => ['nullable', 'string'],
            'secret_key' => ['nullable', 'string'],
            'secret_hash' => ['nullable', 'string'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_name' => ['nullable', 'string', 'max:150'],
        ]);

        $credentials = array_filter([
            'public_key' => $request->input('public_key'),
            'secret_key' => $request->input('secret_key'),
            'secret_hash' => $request->input('secret_hash'),
        ]);

        $metadata = array_filter([
            'bank_name' => $request->input('bank_name'),
            'account_number' => $request->input('account_number'),
            'account_name' => $request->input('account_name'),
        ]);

        $paymentGateway->update([
            'is_active' => $request->boolean('is_active'),
            'credentials' => $credentials ?: $paymentGateway->credentials,
            'metadata' => $metadata ?: $paymentGateway->metadata,
        ]);

        ActivityLog::record(auth()->id(), 'admin_updated_payment_gateway', ['gateway' => $paymentGateway->slug]);

        return back()->with('status', 'gateway-updated');
    }
}

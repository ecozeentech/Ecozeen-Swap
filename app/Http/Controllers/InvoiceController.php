<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\CryptoAsset;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function index(Request $request): View
    {
        $invoices = $request->user()->invoices()->latest()->paginate(10);

        return view('invoices.index', ['invoices' => $invoices]);
    }

    public function create(): View
    {
        return view('invoices.create', ['cryptoAssets' => CryptoAsset::query()->active()->get()]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'crypto_asset_id' => ['required', 'exists:crypto_assets,id'],
            'amount_crypto' => ['required', 'numeric', 'min:0.00000001'],
            'recipient_email' => ['required', 'email'],
            'recipient_name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'due_date' => ['nullable', 'date', 'after:today'],
        ]);

        $user = $request->user();

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'user_id' => $user->id,
            'crypto_asset_id' => $request->input('crypto_asset_id'),
            'amount_crypto' => $request->input('amount_crypto'),
            'recipient_email' => $request->input('recipient_email'),
            'recipient_name' => $request->input('recipient_name'),
            'description' => $request->input('description'),
            'status' => 'sent',
            'due_date' => $request->input('due_date'),
        ]);

        ActivityLog::record($user->id, 'invoice_created', ['invoice_number' => $invoice->invoice_number]);

        return redirect()->route('invoices.show', $invoice)->with('status', 'invoice-created');
    }

    public function show(Request $request, Invoice $invoice): View
    {
        abort_unless($invoice->user_id === $request->user()->id, 403);

        return view('invoices.show', ['invoice' => $invoice]);
    }
}

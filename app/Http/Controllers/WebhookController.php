<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FlutterwaveService;
use App\Services\PaystackService;
use App\Services\TradeService;
use App\Services\WalletService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected PaystackService $paystack,
        protected FlutterwaveService $flutterwave,
        protected TradeService $trades,
        protected WalletService $wallets,
    ) {}

    public function paystack(Request $request): Response
    {
        $signature = $request->header('x-paystack-signature');

        if (! $this->paystack->verifyWebhookSignature($request->getContent(), $signature)) {
            Log::warning('Invalid Paystack webhook signature received.');

            return response('Invalid signature', 400);
        }

        $payload = $request->json()->all();

        if (($payload['event'] ?? null) === 'charge.success') {
            $reference = $payload['data']['reference'] ?? null;
            $this->settleTransaction($reference);
        }

        return response('OK', 200);
    }

    public function flutterwave(Request $request): Response
    {
        $signature = $request->header('verif-hash');

        if (! $this->flutterwave->verifyWebhookSignature($signature)) {
            Log::warning('Invalid Flutterwave webhook signature received.');

            return response('Invalid signature', 400);
        }

        $payload = $request->json()->all();
        $status = $payload['data']['status'] ?? null;

        if ($status === 'successful') {
            $reference = $payload['data']['tx_ref'] ?? null;
            $this->settleTransaction($reference);
        }

        return response('OK', 200);
    }

    protected function settleTransaction(?string $reference): void
    {
        if (! $reference) {
            return;
        }

        $transaction = Transaction::query()->where('reference', $reference)->first();

        if (! $transaction || $transaction->status === 'completed') {
            return;
        }

        DB::transaction(function () use ($transaction) {
            if ($transaction->type === 'buy') {
                $this->trades->completeBuy($transaction);

                return;
            }

            if ($transaction->type === 'deposit') {
                $wallet = $this->wallets->getOrCreateWallet($transaction->user, 'fiat', $transaction->currency_code);
                $wallet->increment('balance', $transaction->amount);
                $transaction->update(['status' => 'completed', 'processed_at' => now()]);
            }
        });
    }
}

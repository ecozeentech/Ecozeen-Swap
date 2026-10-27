<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\FlutterwaveService;
use App\Services\PaymentSettlement;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        protected PaystackService $paystack,
        protected FlutterwaveService $flutterwave,
        protected PaymentSettlement $settlements,
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

        if (! $transaction || ! in_array($transaction->type, ['buy', 'deposit'], true)) {
            return;
        }

        $this->settlements->settle($transaction);
    }
}

<?php

namespace App\Services;

use App\Models\PaymentGateway;
use GuzzleHttp\Client;
use RuntimeException;

class FlutterwaveService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.flutterwave.com/v3/']);
    }

    protected function secretKey(): string
    {
        $gateway = PaymentGateway::query()->where('slug', 'flutterwave')->first();
        $key = $gateway?->credentials['secret_key'] ?? config('services.flutterwave.secret_key');

        if (! $key) {
            throw new RuntimeException('Flutterwave is not configured. Add credentials in the admin panel.');
        }

        return $key;
    }

    protected function secretHash(): string
    {
        $gateway = PaymentGateway::query()->where('slug', 'flutterwave')->first();

        return $gateway?->credentials['secret_hash'] ?? config('services.flutterwave.secret_hash', '');
    }

    public function isActive(): bool
    {
        return (bool) PaymentGateway::query()->where('slug', 'flutterwave')->value('is_active');
    }

    public function initializeTransaction(string $email, float $amount, string $currency, string $reference, array $metadata = []): array
    {
        $response = $this->client->post('payments', [
            'headers' => ['Authorization' => 'Bearer '.$this->secretKey()],
            'json' => [
                'tx_ref' => $reference,
                'amount' => $amount,
                'currency' => $currency,
                'redirect_url' => route('wallet.fund.callback', ['gateway' => 'flutterwave']),
                'customer' => ['email' => $email],
                'meta' => $metadata,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function verifyTransaction(string $transactionId): array
    {
        $response = $this->client->get("transactions/{$transactionId}/verify", [
            'headers' => ['Authorization' => 'Bearer '.$this->secretKey()],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function verifyWebhookSignature(?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        return hash_equals($this->secretHash(), $signature);
    }
}

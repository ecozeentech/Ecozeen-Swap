<?php

namespace App\Services;

use App\Models\PaymentGateway;
use GuzzleHttp\Client;
use RuntimeException;

class PaystackService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = new Client(['base_uri' => 'https://api.paystack.co/']);
    }

    protected function secretKey(): string
    {
        $gateway = PaymentGateway::query()->where('slug', 'paystack')->first();
        $key = $gateway?->credentials['secret_key'] ?? config('services.paystack.secret_key');

        if (! $key) {
            throw new RuntimeException('Paystack is not configured. Add credentials in the admin panel.');
        }

        return $key;
    }

    public function isActive(): bool
    {
        return (bool) PaymentGateway::query()->where('slug', 'paystack')->value('is_active');
    }

    public function initializeTransaction(string $email, float $amountInSubunit, string $reference, array $metadata = []): array
    {
        $response = $this->client->post('transaction/initialize', [
            'headers' => ['Authorization' => 'Bearer '.$this->secretKey()],
            'json' => [
                'email' => $email,
                'amount' => (int) round($amountInSubunit),
                'reference' => $reference,
                'callback_url' => route('wallet.fund.callback', ['gateway' => 'paystack']),
                'metadata' => $metadata,
            ],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function verifyTransaction(string $reference): array
    {
        $response = $this->client->get("transaction/verify/{$reference}", [
            'headers' => ['Authorization' => 'Bearer '.$this->secretKey()],
        ]);

        return json_decode((string) $response->getBody(), true);
    }

    public function verifyWebhookSignature(string $payload, ?string $signature): bool
    {
        if (! $signature) {
            return false;
        }

        $expected = hash_hmac('sha512', $payload, $this->secretKey());

        return hash_equals($expected, $signature);
    }
}

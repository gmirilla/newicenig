<?php

namespace App\Services\Paystack;

use App\Settings\PaystackSettings;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class PaystackClient
{
    public function __construct(
        protected string $secretKey = '',
        protected string $baseUrl = '',
    ) {
        if (blank($this->secretKey) || blank($this->baseUrl)) {
            $settings = app(PaystackSettings::class);

            $this->secretKey = $this->secretKey ?: $settings->secret_key;
            $this->baseUrl = $this->baseUrl ?: $settings->payment_url;
        }
    }

    /**
     * Initialize a transaction and return Paystack's response payload,
     * including the authorization_url to redirect the payer to.
     *
     * @return array<string, mixed>
     */
    public function initializeTransaction(string $email, int $amountInKobo, string $reference, string $callbackUrl, array $metadata = []): array
    {
        $response = $this->client()->post('/transaction/initialize', [
            'email' => $email,
            'amount' => $amountInKobo,
            'reference' => $reference,
            'callback_url' => $callbackUrl,
            'metadata' => $metadata,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Unable to initialize Paystack transaction: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Verify a transaction by its reference.
     *
     * @return array<string, mixed>
     */
    public function verifyTransaction(string $reference): array
    {
        $response = $this->client()->get("/transaction/verify/{$reference}");

        if ($response->failed()) {
            throw new RuntimeException('Unable to verify Paystack transaction: '.$response->body());
        }

        return $response->json();
    }

    /**
     * Verify that a webhook payload was genuinely signed by Paystack.
     */
    public function verifyWebhookSignature(string $rawBody, ?string $signature): bool
    {
        if (blank($signature) || blank($this->secretKey)) {
            return false;
        }

        $expected = hash_hmac('sha512', $rawBody, $this->secretKey);

        return hash_equals($expected, $signature);
    }

    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::baseUrl($this->baseUrl)
            ->withToken($this->secretKey)
            ->acceptJson();
    }
}

<?php

namespace App\Infrastructure\Payment;

use App\Domain\Payment\Contracts\PaymentProvider;
use App\Models\PaymentAttempt;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class CamPayPaymentProvider implements PaymentProvider
{
    public function key(): string
    {
        return 'campay';
    }

    public function initiate(PaymentAttempt $attempt): string
    {
        $attempt->loadMissing(['order.customer', 'order.plan']);

        $response = $this->client()
            ->post('collect/', [
                'amount' => (string) $attempt->order->amount_minor,
                'currency' => $attempt->order->currency,
                'from' => $this->normalizePhone($attempt->order->customer->phone),
                'description' => 'Mirev Access - '.$attempt->order->plan->name,
                'external_reference' => $attempt->idempotency_key,
            ])
            ->throw()
            ->json();

        $reference = $response['reference'] ?? null;

        if (! is_string($reference) || $reference === '') {
            throw new RuntimeException('CamPay n’a retourné aucune référence de paiement.');
        }

        return $reference;
    }

    public function status(PaymentAttempt $attempt): string
    {
        if (! $attempt->external_reference) {
            return 'failed';
        }

        $response = $this->client()
            ->get('transaction/'.$attempt->external_reference.'/')
            ->throw()
            ->json();

        return match (strtoupper((string) ($response['status'] ?? ''))) {
            'SUCCESSFUL', 'SUCCESS' => 'succeeded',
            'FAILED' => 'failed',
            'CANCELLED', 'CANCELED' => 'cancelled',
            default => 'pending',
        };
    }

    private function client(): PendingRequest
    {
        return Http::baseUrl(rtrim((string) config('services.campay.base_url'), '/').'/')
            ->acceptJson()
            ->asJson()
            ->withToken($this->token())
            ->timeout(20)
            ->retry(2, 250);
    }

    private function token(): string
    {
        return Cache::remember('campay.access_token', now()->addMinutes(50), function (): string {
            $username = (string) config('services.campay.username');
            $password = (string) config('services.campay.password');

            if ($username === '' || $password === '') {
                throw new RuntimeException('Les identifiants CamPay ne sont pas configurés.');
            }

            $response = Http::baseUrl(rtrim((string) config('services.campay.base_url'), '/').'/')
                ->acceptJson()
                ->asJson()
                ->timeout(20)
                ->post('token/', [
                    'username' => $username,
                    'password' => $password,
                ])
                ->throw()
                ->json();

            $token = $response['token'] ?? null;

            if (! is_string($token) || $token === '') {
                throw new RuntimeException('CamPay n’a retourné aucun jeton.');
            }

            return $token;
        });
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';

        if (str_starts_with($digits, '237')) {
            return $digits;
        }

        return '237'.ltrim($digits, '0');
    }
}

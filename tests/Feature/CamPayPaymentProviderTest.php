<?php

namespace Tests\Feature;

use App\Domain\Payment\Actions\ReconcilePendingPayments;
use App\Domain\Payment\Actions\StartCheckout;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CamPayPaymentProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_campay_collection_is_confirmed_by_reconciliation(): void
    {
        config()->set('services.campay.base_url', 'https://demo.campay.net/api/');
        config()->set('services.campay.username', 'sandbox-user');
        config()->set('services.campay.password', 'sandbox-password');
        Cache::forget('campay.access_token');

        Http::fakeSequence()
            ->push(['token' => 'sandbox-token'])
            ->push(['reference' => 'campay-reference-001'])
            ->push(['status' => 'SUCCESSFUL']);

        $site = Site::query()->create([
            'name' => 'Mirev CamPay',
            'slug' => 'mirev-campay',
            'currency' => 'XAF',
        ]);

        $plan = Plan::query()->create([
            'site_id' => $site->id,
            'name' => 'Pass CamPay',
            'price_minor' => 500,
            'validity_minutes' => 1440,
        ]);

        $attempt = app(StartCheckout::class)->execute(
            $site,
            $plan,
            '+237 600 000 013',
            'campay',
        );

        $this->assertSame('campay', $attempt->provider);
        $this->assertSame('campay-reference-001', $attempt->external_reference);
        $this->assertSame('pending', $attempt->status);

        $results = app(ReconcilePendingPayments::class)->execute();

        $this->assertSame(1, $results['confirmed']);
        $this->assertSame('succeeded', $attempt->fresh()->status);
        $this->assertSame('paid', $attempt->order->fresh()->status);
        $this->assertNotNull($attempt->order->fresh()->subscription);

        Http::assertSent(fn ($request): bool => $request->url() === 'https://demo.campay.net/api/token/');
        Http::assertSent(fn ($request): bool => $request->url() === 'https://demo.campay.net/api/collect/'
            && $request['from'] === '237600000013'
            && $request['amount'] === '500');
        Http::assertSent(fn ($request): bool => $request->url() === 'https://demo.campay.net/api/transaction/campay-reference-001/');
    }
}

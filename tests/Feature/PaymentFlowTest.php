<?php

namespace Tests\Feature;

use App\Domain\Payment\Actions\ConfirmPayment;
use App\Models\PaymentAttempt;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_access_is_not_created_before_payment_confirmation(): void
    {
        [$site, $plan] = $this->siteAndPlan();

        $response = $this->post(route('client.purchase', $site), [
            'phone' => '+237600000010',
            'plan_id' => $plan->id,
        ]);

        $attempt = PaymentAttempt::query()->firstOrFail();

        $response->assertRedirect(route('payments.show', $attempt));
        $this->assertSame('pending', $attempt->status);
        $this->assertSame('pending', $attempt->order->status);
        $this->assertDatabaseCount('subscriptions', 0);
        $this->assertDatabaseCount('access_grants', 0);
    }

    public function test_confirmed_payment_activates_exactly_one_subscription(): void
    {
        [$site, $plan] = $this->siteAndPlan();

        $this->post(route('client.purchase', $site), [
            'phone' => '+237600000011',
            'plan_id' => $plan->id,
        ]);

        $attempt = PaymentAttempt::query()->firstOrFail();
        $confirm = app(ConfirmPayment::class);

        $first = $confirm->execute($attempt, ['event_id' => 'event-001']);
        $second = $confirm->execute($attempt->fresh(), ['event_id' => 'event-001']);

        $this->assertTrue($first->is($second));
        $this->assertSame('succeeded', $attempt->fresh()->status);
        $this->assertSame('paid', $attempt->order->fresh()->status);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('access_grants', 1);
        $this->assertSame('active', $first->accessGrant->status);
    }

    public function test_local_simulator_completes_the_customer_flow(): void
    {
        [$site, $plan] = $this->siteAndPlan();

        $this->post(route('client.purchase', $site), [
            'phone' => '+237600000012',
            'plan_id' => $plan->id,
        ]);

        $attempt = PaymentAttempt::query()->firstOrFail();

        $this->post(route('payments.simulate-success', $attempt))
            ->assertRedirect();

        $this->assertSame('succeeded', $attempt->fresh()->status);
        $this->assertNotNull($attempt->order->fresh()->subscription);
    }

    private function siteAndPlan(): array
    {
        $site = Site::query()->create([
            'name' => 'Mirev Payment',
            'slug' => 'mirev-payment',
            'currency' => 'XAF',
        ]);

        $plan = Plan::query()->create([
            'site_id' => $site->id,
            'name' => 'Pass Paiement',
            'price_minor' => 500,
            'validity_minutes' => 1440,
        ]);

        return [$site, $plan];
    }
}

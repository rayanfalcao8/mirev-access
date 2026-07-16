<?php

namespace Tests\Feature;

use App\Domain\Subscription\Actions\ActivateSimulatedPurchase;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SimulatedPurchaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_customer_can_buy_a_plan_and_receive_network_access(): void
    {
        $site = Site::query()->create([
            'name' => 'Mirev Bastos',
            'slug' => 'bastos',
            'currency' => 'XAF',
        ]);

        $plan = Plan::query()->create([
            'site_id' => $site->id,
            'name' => 'Pass Jour',
            'price_minor' => 500,
            'validity_minutes' => 1440,
            'download_limit_kbps' => 5000,
        ]);

        $subscription = app(ActivateSimulatedPurchase::class)->execute(
            $site,
            $plan,
            '+237600000000',
            'payment_test_001',
        );

        $this->assertSame('active', $subscription->status);
        $this->assertSame('active', $subscription->accessGrant->status);
        $this->assertStringStartsWith('fake_', $subscription->accessGrant->external_reference);
        $this->assertDatabaseHas('orders', [
            'payment_reference' => 'payment_test_001',
            'status' => 'paid',
        ]);
    }

    public function test_replaying_a_payment_reference_does_not_duplicate_access(): void
    {
        $site = Site::query()->create([
            'name' => 'Mirev Bastos',
            'slug' => 'bastos',
            'currency' => 'XAF',
        ]);

        $plan = Plan::query()->create([
            'site_id' => $site->id,
            'name' => 'Pass Jour',
            'price_minor' => 500,
            'validity_minutes' => 1440,
        ]);

        $action = app(ActivateSimulatedPurchase::class);

        $first = $action->execute($site, $plan, '+237600000000', 'same_payment');
        $second = $action->execute($site, $plan, '+237600000000', 'same_payment');

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('orders', 1);
        $this->assertDatabaseCount('subscriptions', 1);
        $this->assertDatabaseCount('access_grants', 1);
    }
}

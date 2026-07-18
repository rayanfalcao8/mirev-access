<?php

namespace Tests\Feature;

use App\Domain\Subscription\Actions\ActivateSimulatedPurchase;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class SubscriptionOperationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_due_subscription_is_expired_and_network_access_is_revoked(): void
    {
        [$site, $plan] = $this->siteAndPlan();

        $subscription = app(ActivateSimulatedPurchase::class)->execute(
            $site,
            $plan,
            '+237600000001',
            'expired_payment',
        );

        $subscription->update(['expires_at' => now()->subMinute()]);

        Artisan::call('subscriptions:expire');

        $subscription->refresh();

        $this->assertSame('expired', $subscription->status);
        $this->assertSame('revoked', $subscription->accessGrant->status);
        $this->assertNotNull($subscription->accessGrant->revoked_at);
        $this->assertDatabaseHas('subscription_transitions', [
            'subscription_id' => $subscription->id,
            'from_status' => 'active',
            'to_status' => 'expired',
            'reason' => 'validity_period_elapsed',
        ]);
    }

    public function test_expiration_command_is_idempotent(): void
    {
        [$site, $plan] = $this->siteAndPlan();

        $subscription = app(ActivateSimulatedPurchase::class)->execute(
            $site,
            $plan,
            '+237600000002',
            'idempotent_expiry',
        );

        $subscription->update(['expires_at' => now()->subMinute()]);

        Artisan::call('subscriptions:expire');
        Artisan::call('subscriptions:expire');

        $this->assertDatabaseCount('subscription_transitions', 2);
        $this->assertDatabaseCount('incidents', 0);
    }

    private function siteAndPlan(): array
    {
        $site = Site::query()->create([
            'name' => 'Mirev Operations',
            'slug' => 'operations',
            'currency' => 'XAF',
        ]);

        $plan = Plan::query()->create([
            'site_id' => $site->id,
            'name' => 'Pass Minute',
            'price_minor' => 100,
            'validity_minutes' => 1,
        ]);

        return [$site, $plan];
    }
}

<?php

namespace Tests\Feature;

use App\Domain\Subscription\Actions\ActivateSimulatedPurchase;
use App\Domain\Subscription\Actions\ManageSubscription;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ManageSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_suspend_and_reactivate_a_subscription(): void
    {
        $subscription = $this->subscription();

        app(ManageSubscription::class)->suspend($subscription, 42);

        $subscription->refresh();
        $this->assertSame('suspended', $subscription->status);
        $this->assertSame('revoked', $subscription->accessGrant->status);
        $this->assertDatabaseHas('subscription_transitions', [
            'subscription_id' => $subscription->id,
            'to_status' => 'suspended',
            'reason' => 'admin_suspended',
        ]);

        app(ManageSubscription::class)->reactivate($subscription, 42);

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertSame('active', $subscription->accessGrant->status);
        $this->assertNull($subscription->accessGrant->revoked_at);
    }

    public function test_extending_an_expired_subscription_reactivates_network_access(): void
    {
        $subscription = $this->subscription();
        $subscription->update(['expires_at' => now()->subMinute()]);

        app(ManageSubscription::class)->expire($subscription, 42);
        app(ManageSubscription::class)->extend($subscription->fresh(), 1440, 42);

        $subscription->refresh();
        $this->assertSame('active', $subscription->status);
        $this->assertTrue($subscription->expires_at->isFuture());
        $this->assertSame('active', $subscription->accessGrant->status);
        $this->assertDatabaseHas('subscription_transitions', [
            'subscription_id' => $subscription->id,
            'to_status' => 'active',
            'reason' => 'admin_extended_and_reactivated',
        ]);
    }

    public function test_reconciliation_restores_missing_access_for_active_subscription(): void
    {
        $subscription = $this->subscription();
        $subscription->accessGrant()->delete();

        app(ManageSubscription::class)->reconcile($subscription->fresh(), 42);

        $subscription->refresh();
        $this->assertSame('active', $subscription->accessGrant->status);
        $this->assertNotNull($subscription->accessGrant->external_reference);
        $this->assertDatabaseHas('subscription_transitions', [
            'subscription_id' => $subscription->id,
            'reason' => 'admin_reconciled',
        ]);
    }

    private function subscription()
    {
        $site = Site::query()->create([
            'name' => 'Mirev Admin',
            'slug' => 'mirev-admin',
            'currency' => 'XAF',
        ]);

        $plan = Plan::query()->create([
            'site_id' => $site->id,
            'name' => 'Pass Admin',
            'price_minor' => 500,
            'validity_minutes' => 1440,
        ]);

        return app(ActivateSimulatedPurchase::class)->execute(
            $site,
            $plan,
            '+237600000099',
            'admin_management_test',
        );
    }
}

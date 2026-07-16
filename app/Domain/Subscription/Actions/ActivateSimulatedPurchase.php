<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Models\AccessGrant;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Plan;
use App\Models\Site;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class ActivateSimulatedPurchase
{
    public function __construct(
        private readonly NetworkAccessProvider $network,
    ) {}

    public function execute(
        Site $site,
        Plan $plan,
        string $phone,
        string $paymentReference,
    ): Subscription {
        return DB::transaction(function () use ($site, $plan, $phone, $paymentReference) {
            $existingOrder = Order::query()
                ->where('payment_reference', $paymentReference)
                ->first();

            if ($existingOrder?->subscription) {
                return $existingOrder->subscription;
            }

            $customer = Customer::query()->firstOrCreate(['phone' => $phone]);

            $order = Order::query()->create([
                'site_id' => $site->id,
                'plan_id' => $plan->id,
                'customer_id' => $customer->id,
                'status' => 'paid',
                'amount_minor' => $plan->price_minor,
                'currency' => $site->currency,
                'payment_reference' => $paymentReference,
                'paid_at' => now(),
            ]);

            $subscription = Subscription::query()->create([
                'order_id' => $order->id,
                'customer_id' => $customer->id,
                'plan_id' => $plan->id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMinutes($plan->validity_minutes),
            ]);

            $subscription->transitions()->create([
                'from_status' => null,
                'to_status' => 'active',
                'reason' => 'payment_confirmed',
            ]);

            $grant = AccessGrant::query()->create([
                'subscription_id' => $subscription->id,
                'provider' => 'fake',
            ]);

            $grant->update([
                'status' => 'active',
                'external_reference' => $this->network->authorize($grant),
                'authorized_at' => now(),
            ]);

            return $subscription->fresh(['plan', 'accessGrant']);
        });
    }
}

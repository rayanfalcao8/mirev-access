<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Access\Services\NetworkProviderRegistry;
use App\Models\AccessGrant;
use App\Models\PaymentAttempt;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class ConfirmPayment
{
    public function __construct(
        private readonly NetworkProviderRegistry $networkProviders,
    ) {}

    public function execute(PaymentAttempt $attempt, array $metadata = []): Subscription
    {
        return DB::transaction(function () use ($attempt, $metadata) {
            $locked = PaymentAttempt::query()
                ->with(['order.subscription', 'order.plan.site.networkConnection'])
                ->lockForUpdate()
                ->findOrFail($attempt->id);

            if ($locked->order->subscription) {
                return $locked->order->subscription;
            }

            $order = $locked->order;
            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'payment_reference' => $locked->external_reference,
            ]);

            $locked->update([
                'status' => 'succeeded',
                'metadata' => array_merge($locked->metadata ?? [], $metadata),
                'completed_at' => now(),
            ]);

            $subscription = Subscription::query()->create([
                'order_id' => $order->id,
                'customer_id' => $order->customer_id,
                'plan_id' => $order->plan_id,
                'status' => 'active',
                'starts_at' => now(),
                'expires_at' => now()->addMinutes($order->plan->validity_minutes),
            ]);

            $subscription->transitions()->create([
                'from_status' => null,
                'to_status' => 'active',
                'reason' => 'payment_confirmed',
                'metadata' => [
                    'payment_attempt_id' => $locked->id,
                    'payment_provider' => $locked->provider,
                ],
            ]);

            $site = $order->plan->site;
            $connection = $site->networkConnection()->firstOrCreate(
                [],
                ['provider' => 'fake', 'status' => 'unconfigured'],
            );

            $grant = AccessGrant::query()->create([
                'subscription_id' => $subscription->id,
                'provider' => $connection->provider,
            ]);

            $grant->update([
                'status' => 'active',
                'external_reference' => $this->networkProviders->for($connection->provider)->authorize($grant, $connection),
                'authorized_at' => now(),
            ]);

            return $subscription->fresh(['plan', 'accessGrant']);
        });
    }
}

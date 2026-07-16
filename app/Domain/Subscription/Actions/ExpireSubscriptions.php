<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Access\Actions\RevokeAccessGrant;
use App\Domain\Subscription\Services\SubscriptionStateManager;
use App\Models\Subscription;

class ExpireSubscriptions
{
    public function __construct(
        private readonly SubscriptionStateManager $states,
        private readonly RevokeAccessGrant $revoke,
    ) {}

    public function execute(): int
    {
        $expired = 0;

        Subscription::query()
            ->whereIn('status', ['active', 'expiring'])
            ->where('expires_at', '<=', now())
            ->with('accessGrant')
            ->chunkById(100, function ($subscriptions) use (&$expired) {
                foreach ($subscriptions as $subscription) {
                    $this->states->transition(
                        $subscription,
                        'expired',
                        'validity_period_elapsed',
                    );

                    if ($subscription->accessGrant) {
                        $this->revoke->execute(
                            $subscription->accessGrant,
                            'subscription_expired',
                        );
                    }

                    $expired++;
                }
            });

        return $expired;
    }
}

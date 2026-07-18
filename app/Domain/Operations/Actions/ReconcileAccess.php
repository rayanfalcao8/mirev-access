<?php

namespace App\Domain\Operations\Actions;

use App\Domain\Access\Actions\RevokeAccessGrant;
use App\Models\Incident;
use App\Models\Subscription;

class ReconcileAccess
{
    public function __construct(
        private readonly RevokeAccessGrant $revoke,
    ) {}

    public function execute(): array
    {
        $results = ['revoked' => 0, 'incidents' => 0];

        Subscription::query()
            ->where('status', 'expired')
            ->whereHas('accessGrant', fn ($query) => $query->where('status', '!=', 'revoked'))
            ->with('accessGrant')
            ->chunkById(100, function ($subscriptions) use (&$results) {
                foreach ($subscriptions as $subscription) {
                    if ($this->revoke->execute($subscription->accessGrant, 'reconciliation')) {
                        $results['revoked']++;
                    }
                }
            });

        Subscription::query()
            ->whereIn('status', ['active', 'expiring'])
            ->whereDoesntHave('accessGrant')
            ->chunkById(100, function ($subscriptions) use (&$results) {
                foreach ($subscriptions as $subscription) {
                    Incident::report(
                        fingerprint: 'missing-access-grant:'.$subscription->id,
                        type: 'missing_access_grant',
                        title: 'Abonnement sans autorisation réseau',
                        message: 'Un abonnement actif ne possède aucune autorisation réseau.',
                        context: ['subscription_id' => $subscription->id],
                        severity: 'critical',
                    );

                    $results['incidents']++;
                }
            });

        return $results;
    }
}

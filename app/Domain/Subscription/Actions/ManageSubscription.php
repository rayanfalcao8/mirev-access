<?php

namespace App\Domain\Subscription\Actions;

use App\Domain\Access\Actions\RevokeAccessGrant;
use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Domain\Subscription\Services\SubscriptionStateManager;
use App\Models\AccessGrant;
use App\Models\Subscription;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ManageSubscription
{
    public function __construct(
        private readonly SubscriptionStateManager $states,
        private readonly RevokeAccessGrant $revoke,
        private readonly NetworkProviderRegistry $providers,
    ) {}

    public function suspend(Subscription $subscription, ?int $actorId = null): Subscription
    {
        if (! in_array($subscription->status, ['active', 'expiring'], true)) {
            throw new RuntimeException('Seul un abonnement actif peut être suspendu.');
        }

        return DB::transaction(function () use ($subscription, $actorId) {
            $updated = $this->states->transition(
                $subscription,
                'suspended',
                'admin_suspended',
                ['actor_id' => $actorId],
            );

            if ($updated->accessGrant) {
                $this->revoke->execute($updated->accessGrant, 'admin_suspended');
            }

            return $updated->fresh(['accessGrant', 'transitions']);
        });
    }

    public function reactivate(Subscription $subscription, ?int $actorId = null): Subscription
    {
        if ($subscription->status !== 'suspended') {
            throw new RuntimeException('Seul un abonnement suspendu peut être réactivé.');
        }

        if ($subscription->expires_at->isPast()) {
            throw new RuntimeException('Prolongez d’abord cet abonnement expiré.');
        }

        return DB::transaction(function () use ($subscription, $actorId) {
            $this->authorize($subscription);

            return $this->states->transition(
                $subscription,
                'active',
                'admin_reactivated',
                ['actor_id' => $actorId],
            )->fresh(['accessGrant', 'transitions']);
        });
    }

    public function extend(Subscription $subscription, int $minutes, ?int $actorId = null): Subscription
    {
        if ($minutes < 1) {
            throw new RuntimeException('La prolongation doit être supérieure à zéro.');
        }

        return DB::transaction(function () use ($subscription, $minutes, $actorId) {
            $base = $subscription->expires_at->isFuture()
                ? $subscription->expires_at
                : Carbon::now();

            $subscription->update(['expires_at' => $base->copy()->addMinutes($minutes)]);

            if ($subscription->status === 'expired') {
                $this->authorize($subscription);
                $subscription = $this->states->transition(
                    $subscription,
                    'active',
                    'admin_extended_and_reactivated',
                    ['actor_id' => $actorId, 'minutes' => $minutes],
                );
            } else {
                $subscription->transitions()->create([
                    'from_status' => $subscription->status,
                    'to_status' => $subscription->status,
                    'reason' => 'admin_extended',
                    'metadata' => ['actor_id' => $actorId, 'minutes' => $minutes],
                ]);
            }

            return $subscription->fresh(['accessGrant', 'transitions']);
        });
    }

    public function expire(Subscription $subscription, ?int $actorId = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $actorId) {
            $updated = $this->states->transition(
                $subscription,
                'expired',
                'admin_expired',
                ['actor_id' => $actorId],
            );

            if ($updated->accessGrant) {
                $this->revoke->execute($updated->accessGrant, 'admin_expired');
            }

            return $updated->fresh(['accessGrant', 'transitions']);
        });
    }

    public function reconcile(Subscription $subscription, ?int $actorId = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $actorId) {
            if (in_array($subscription->status, ['expired', 'suspended'], true)) {
                if ($subscription->accessGrant && $subscription->accessGrant->status !== 'revoked') {
                    $this->revoke->execute($subscription->accessGrant, 'admin_reconciliation');
                }
            } elseif (in_array($subscription->status, ['active', 'expiring'], true)) {
                $this->authorize($subscription);
            }

            $subscription->transitions()->create([
                'from_status' => $subscription->status,
                'to_status' => $subscription->status,
                'reason' => 'admin_reconciled',
                'metadata' => ['actor_id' => $actorId],
            ]);

            return $subscription->fresh(['accessGrant', 'transitions']);
        });
    }

    private function authorize(Subscription $subscription): AccessGrant
    {
        $grant = $subscription->accessGrant()->firstOrCreate([
            'provider' => 'fake',
        ]);

        if ($grant->status === 'active') {
            return $grant;
        }

        $grant->update([
            'status' => 'active',
            'external_reference' => $this->providers->for($connection->provider)->authorize($grant, $connection),
            'authorized_at' => now(),
            'revoked_at' => null,
            'last_error' => null,
        ]);

        return $grant;
    }
}

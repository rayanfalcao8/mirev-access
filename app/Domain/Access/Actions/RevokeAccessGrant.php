<?php

namespace App\Domain\Access\Actions;

use App\Domain\Access\Services\NetworkProviderRegistry;
use App\Models\AccessGrant;
use App\Models\Incident;
use Throwable;

class RevokeAccessGrant
{
    public function __construct(
        private readonly NetworkProviderRegistry $providers,
    ) {}

    public function execute(AccessGrant $grant, string $reason): bool
    {
        if ($grant->status === 'revoked') {
            return true;
        }

        try {
            $connection = $grant->subscription->plan->site->networkConnection;

            if (! $connection) {
                throw new \RuntimeException('Aucune connexion réseau n’est configurée pour ce site.');
            }

            $this->providers->for($grant->provider)->disconnect($grant, $connection);

            $grant->update([
                'status' => 'revoked',
                'revoked_at' => now(),
                'last_error' => null,
            ]);

            return true;
        } catch (Throwable $exception) {
            $grant->update([
                'status' => 'revocation_failed',
                'last_error' => $exception->getMessage(),
            ]);

            Incident::report(
                fingerprint: 'access-revocation:'.$grant->id,
                type: 'access_revocation_failed',
                title: 'Échec de révocation réseau',
                message: $exception->getMessage(),
                context: [
                    'access_grant_id' => $grant->id,
                    'subscription_id' => $grant->subscription_id,
                    'reason' => $reason,
                ],
                severity: 'critical',
            );

            return false;
        }
    }
}

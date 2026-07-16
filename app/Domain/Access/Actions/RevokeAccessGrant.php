<?php

namespace App\Domain\Access\Actions;

use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Models\AccessGrant;
use App\Models\Incident;
use Throwable;

class RevokeAccessGrant
{
    public function __construct(
        private readonly NetworkAccessProvider $network,
    ) {}

    public function execute(AccessGrant $grant, string $reason): bool
    {
        if ($grant->status === 'revoked') {
            return true;
        }

        try {
            $this->network->disconnect($grant);

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

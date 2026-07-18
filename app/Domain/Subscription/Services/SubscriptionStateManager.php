<?php

namespace App\Domain\Subscription\Services;

use App\Models\Subscription;
use Illuminate\Support\Facades\DB;

class SubscriptionStateManager
{
    public function transition(
        Subscription $subscription,
        string $toStatus,
        string $reason,
        array $metadata = [],
    ): Subscription {
        return DB::transaction(function () use ($subscription, $toStatus, $reason, $metadata) {
            $locked = Subscription::query()->lockForUpdate()->findOrFail($subscription->id);

            if ($locked->status === $toStatus) {
                return $locked;
            }

            $fromStatus = $locked->status;
            $locked->update(['status' => $toStatus]);
            $locked->transitions()->create([
                'from_status' => $fromStatus,
                'to_status' => $toStatus,
                'reason' => $reason,
                'metadata' => $metadata,
            ]);

            return $locked->fresh();
        });
    }
}

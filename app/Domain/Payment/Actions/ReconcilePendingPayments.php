<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentProviderRegistry;
use App\Models\PaymentAttempt;
use Throwable;

class ReconcilePendingPayments
{
    public function __construct(
        private readonly PaymentProviderRegistry $providers,
        private readonly ConfirmPayment $confirm,
    ) {}

    public function execute(): array
    {
        $results = ['confirmed' => 0, 'failed' => 0, 'pending' => 0, 'errors' => 0];

        PaymentAttempt::query()
            ->where('status', 'pending')
            ->whereNotNull('external_reference')
            ->with('order')
            ->chunkById(100, function ($attempts) use (&$results): void {
                foreach ($attempts as $attempt) {
                    try {
                        $status = $this->providers->for($attempt->provider)->status($attempt);

                        if ($status === 'succeeded') {
                            $this->confirm->execute($attempt, ['source' => 'provider_reconciliation']);
                            $results['confirmed']++;
                        } elseif (in_array($status, ['failed', 'cancelled'], true)) {
                            $attempt->update(['status' => $status, 'completed_at' => now()]);
                            $attempt->order->update(['status' => $status === 'failed' ? 'failed' : 'cancelled']);
                            $results['failed']++;
                        } else {
                            $results['pending']++;
                        }
                    } catch (Throwable $exception) {
                        $attempt->update([
                            'metadata' => array_merge($attempt->metadata ?? [], [
                                'last_reconciliation_error' => $exception->getMessage(),
                            ]),
                        ]);
                        $results['errors']++;
                    }
                }
            });

        return $results;
    }
}

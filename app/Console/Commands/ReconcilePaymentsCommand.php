<?php

namespace App\Console\Commands;

use App\Domain\Payment\Actions\ReconcilePendingPayments;
use Illuminate\Console\Command;

class ReconcilePaymentsCommand extends Command
{
    protected $signature = 'payments:reconcile';

    protected $description = 'Reconcile pending payments with their providers';

    public function handle(ReconcilePendingPayments $reconcile): int
    {
        $this->info(json_encode($reconcile->execute(), JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}

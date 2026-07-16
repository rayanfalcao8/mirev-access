<?php

namespace App\Console\Commands;

use App\Domain\Operations\Actions\ReconcileAccess;
use Illuminate\Console\Command;

class ReconcileAccessCommand extends Command
{
    protected $signature = 'network:reconcile';

    protected $description = 'Reconcile subscription state with network access grants';

    public function handle(ReconcileAccess $reconcile): int
    {
        $results = $reconcile->execute();
        $this->info(json_encode($results, JSON_THROW_ON_ERROR));

        return self::SUCCESS;
    }
}

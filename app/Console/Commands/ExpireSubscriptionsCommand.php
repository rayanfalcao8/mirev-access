<?php

namespace App\Console\Commands;

use App\Domain\Subscription\Actions\ExpireSubscriptions;
use Illuminate\Console\Command;

class ExpireSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'Expire due subscriptions and revoke their network access';

    public function handle(ExpireSubscriptions $expire): int
    {
        $count = $expire->execute();
        $this->info($count.' abonnement(s) expiré(s).');

        return self::SUCCESS;
    }
}

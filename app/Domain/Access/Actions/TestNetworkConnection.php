<?php

namespace App\Domain\Access\Actions;

use App\Domain\Access\Services\NetworkProviderRegistry;
use App\Models\NetworkConnection;
use Throwable;

class TestNetworkConnection
{
    public function __construct(
        private readonly NetworkProviderRegistry $providers,
    ) {}

    public function execute(NetworkConnection $connection): bool
    {
        try {
            $this->providers->for($connection->provider)->test($connection);

            $connection->update([
                'status' => 'connected',
                'last_tested_at' => now(),
                'last_error' => null,
            ]);

            return true;
        } catch (Throwable $exception) {
            $connection->update([
                'status' => 'failed',
                'last_tested_at' => now(),
                'last_error' => $exception->getMessage(),
            ]);

            return false;
        }
    }
}

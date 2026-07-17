<?php

namespace App\Domain\Access\Contracts;

use App\Models\AccessGrant;
use App\Models\NetworkConnection;

interface SiteNetworkProvider
{
    public function key(): string;

    public function test(NetworkConnection $connection): void;

    public function authorize(AccessGrant $grant, NetworkConnection $connection): string;

    public function disconnect(AccessGrant $grant, NetworkConnection $connection): void;
}

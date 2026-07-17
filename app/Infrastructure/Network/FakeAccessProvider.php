<?php

namespace App\Infrastructure\Network;

use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Domain\Access\Contracts\SiteNetworkProvider;
use App\Models\AccessGrant;
use App\Models\NetworkConnection;
use Illuminate\Support\Str;

class FakeAccessProvider implements NetworkAccessProvider, SiteNetworkProvider
{
    public function key(): string
    {
        return 'fake';
    }

    public function test(NetworkConnection $connection): void
    {
        //
    }

    public function authorize(AccessGrant $grant, ?NetworkConnection $connection = null): string
    {
        return 'fake_'.Str::uuid();
    }

    public function disconnect(AccessGrant $grant, ?NetworkConnection $connection = null): void
    {
        //
    }
}

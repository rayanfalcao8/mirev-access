<?php

namespace App\Infrastructure\Network;

use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Models\AccessGrant;
use Illuminate\Support\Str;

class FakeAccessProvider implements NetworkAccessProvider
{
    public function authorize(AccessGrant $grant): string
    {
        return 'fake_'.Str::uuid();
    }
}

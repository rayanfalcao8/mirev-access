<?php

namespace App\Domain\Access\Contracts;

use App\Models\AccessGrant;

interface NetworkAccessProvider
{
    public function authorize(AccessGrant $grant): string;

    public function disconnect(AccessGrant $grant): void;
}

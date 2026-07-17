<?php

namespace App\Domain\Payment\Contracts;

use App\Models\PaymentAttempt;

interface PaymentProvider
{
    public function key(): string;

    public function initiate(PaymentAttempt $attempt): string;
}

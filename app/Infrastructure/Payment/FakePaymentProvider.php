<?php

namespace App\Infrastructure\Payment;

use App\Domain\Payment\Contracts\PaymentProvider;
use App\Models\PaymentAttempt;
use Illuminate\Support\Str;

class FakePaymentProvider implements PaymentProvider
{
    public function key(): string
    {
        return 'fake';
    }

    public function initiate(PaymentAttempt $attempt): string
    {
        return 'fake_pay_'.Str::uuid();
    }

    public function status(PaymentAttempt $attempt): string
    {
        return $attempt->status;
    }
}

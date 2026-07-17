<?php

namespace App\Domain\Payment\Services;

use App\Domain\Payment\Contracts\PaymentProvider;
use InvalidArgumentException;

class PaymentProviderRegistry
{
    public function __construct(
        private readonly iterable $providers,
    ) {}

    public function for(string $key): PaymentProvider
    {
        foreach ($this->providers as $provider) {
            if ($provider->key() === $key) {
                return $provider;
            }
        }

        throw new InvalidArgumentException("Le fournisseur de paiement [{$key}] n’est pas installé.");
    }
}

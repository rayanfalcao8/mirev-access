<?php

namespace App\Domain\Access\Services;

use App\Domain\Access\Contracts\SiteNetworkProvider;
use InvalidArgumentException;

class NetworkProviderRegistry
{
    public function __construct(
        private readonly iterable $providers,
    ) {}

    public function for(string $key): SiteNetworkProvider
    {
        foreach ($this->providers as $provider) {
            if ($provider->key() === $key) {
                return $provider;
            }
        }

        throw new InvalidArgumentException("Le connecteur réseau [{$key}] n’est pas installé.");
    }

    public function keys(): array
    {
        return array_map(
            fn (SiteNetworkProvider $provider): string => $provider->key(),
            iterator_to_array($this->providers),
        );
    }
}

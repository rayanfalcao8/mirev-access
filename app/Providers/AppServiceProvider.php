<?php

namespace App\Providers;

use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Domain\Access\Services\NetworkProviderRegistry;
use App\Infrastructure\Network\FakeAccessProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FakeAccessProvider::class);
        $this->app->bind(NetworkAccessProvider::class, FakeAccessProvider::class);

        $this->app->singleton(
            NetworkProviderRegistry::class,
            fn ($app) => new NetworkProviderRegistry([
                $app->make(FakeAccessProvider::class),
            ]),
        );
    }

    public function boot(): void
    {
        //
    }
}

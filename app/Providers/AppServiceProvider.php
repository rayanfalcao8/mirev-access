<?php

namespace App\Providers;

use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Domain\Access\Services\NetworkProviderRegistry;
use App\Domain\Payment\Services\PaymentProviderRegistry;
use App\Infrastructure\Network\FakeAccessProvider;
use App\Infrastructure\Payment\CamPayPaymentProvider;
use App\Infrastructure\Payment\FakePaymentProvider;
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

        $this->app->singleton(FakePaymentProvider::class);
        $this->app->singleton(CamPayPaymentProvider::class);
        $this->app->singleton(
            PaymentProviderRegistry::class,
            fn ($app) => new PaymentProviderRegistry([
                $app->make(FakePaymentProvider::class),
                $app->make(CamPayPaymentProvider::class),
            ]),
        );
    }

    public function boot(): void
    {
        //
    }
}

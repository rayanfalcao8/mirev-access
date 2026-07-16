<?php

namespace App\Providers;

use App\Domain\Access\Contracts\NetworkAccessProvider;
use App\Infrastructure\Network\FakeAccessProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NetworkAccessProvider::class, FakeAccessProvider::class);
    }

    public function boot(): void
    {
        //
    }
}

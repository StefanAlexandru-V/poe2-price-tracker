<?php

namespace App\Providers;

use App\Services\PoeNinjaClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PoeNinjaClient::class);
    }

    public function boot(): void
    {
        //
    }
}

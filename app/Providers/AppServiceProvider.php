<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\MongoService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(MongoService::class, function ($app) {
            return new MongoService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

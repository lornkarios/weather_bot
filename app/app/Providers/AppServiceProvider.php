<?php

namespace App\Providers;

use App\Service\WeatherApi\OpenMeteoClient;
use App\Service\WeatherApi\WeatherApiClient;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->app->bind(WeatherApiClient::class, OpenMeteoClient::class);
    }
}

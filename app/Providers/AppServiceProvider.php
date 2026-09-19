<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Services\Twitter\TwitterClient::class,
            function ($app) {
                return match (config('services.twitter.data_source')) {
                    'getxapi' => new \App\Services\Twitter\GetXApiClient(
                        config('services.getxapi.key')
                    ),
                    default => new \App\Services\Twitter\GetXApiClient(
                        config('services.getxapi.key')
                    ),
                };
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}

<?php

namespace App\Providers;

use Carbon\CarbonInterval;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

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
        Passport::tokensExpireIn(CarbonInterval::days(7));
        Passport::refreshTokensExpireIn(CarbonInterval::days(14));
        Passport::personalAccessTokensExpireIn(CarbonInterval::days(14));
        
        if ($this->app->runningInConsole()) {
            $this->commands([]);
        }
    }
}

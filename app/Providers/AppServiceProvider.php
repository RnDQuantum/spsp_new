<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\URL;


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
        Carbon::setLocale('id');
        // Force HTTPS in production environment
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        } else {
            // Force HTTP in local or development environment
            URL::forceScheme('http');
        }
    }
}

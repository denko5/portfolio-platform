<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        // Public read API: 120 requests per minute per IP address.
        RateLimiter::for('public-api', fn (Request $request): Limit => Limit::perMinute(120)->by($request->ip()));

        // Contact form attempts, valid or not: a generous flood guard. Accepted submissions are
        // limited more strictly inside ContactController (5 per hour per IP, 3 per hour per email),
        // so a visitor who mistypes the form is never locked out by their own mistakes.
        RateLimiter::for('contact-attempts', fn (Request $request): Limit => Limit::perHour(60)->by('contact-attempts:'.$request->ip()));
    }
}

<?php

namespace App\Providers;

use App\Models\Navigation;
use App\Observers\NavigationObserver;
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
        // Register Navigation Observer to auto-grant permissions
        Navigation::observe(NavigationObserver::class);
    }
}

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
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
        // Forcer HTTPS en production
        if (config('app.env') === 'production' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            URL::forceScheme('https');
        }

        // Directive Blade pour vérifier si l'utilisateur peut effectuer des actions
        Blade::if('canaction', function () {
            return auth()->check() && auth()->user()->canPerformActions();
        });

        // Directive Blade pour vérifier si l'utilisateur est admin
        Blade::if('admin', function () {
            return auth()->check() && auth()->user()->isAdmin();
        });

        // Directive Blade pour vérifier si l'utilisateur est en mode lecture seule (manager)
        Blade::if('readonly', function () {
            return auth()->check() && auth()->user()->isReadOnly();
        });
    }
}

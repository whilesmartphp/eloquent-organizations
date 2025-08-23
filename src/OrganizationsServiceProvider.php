<?php

namespace Whilesmart\Organizations;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OrganizationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/organizations.php', 'organizations');
    }

    public function boot(): void
    {
        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Publish config
        $this->publishes([
            __DIR__.'/../config/organizations.php' => config_path('organizations.php'),
        ], 'organizations-config');

        // Register routes if enabled
        if (config('organizations.register_routes', true)) {
            $this->registerRoutes();
        }
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => config('organizations.route_prefix', 'api'),
            'middleware' => config('organizations.route_middleware', ['auth:sanctum']),
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/organizations.php');
        });
    }
}

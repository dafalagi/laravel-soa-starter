<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Services\AuthService;
use Modules\Auth\Services\Contracts\AuthServiceInterface;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register service contracts
        $this->app->bind(AuthServiceInterface::class, AuthService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module routes
        $this->loadRoutes();
        
        // Load module migrations
        $this->loadMigrations();
        
        // Register module commands
        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    /**
     * Load module routes.
     */
    private function loadRoutes(): void
    {
        if (file_exists(__DIR__ . '/../Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        }
    }

    /**
     * Load module migrations.
     */
    private function loadMigrations(): void
    {
        $migrationPath = __DIR__ . '/../Database/Migrations';
        if (is_dir($migrationPath)) {
            $this->loadMigrationsFrom($migrationPath);
        }
    }

    /**
     * Register module commands.
     */
    private function registerCommands(): void
    {
        // Register any module-specific commands here
        // $this->commands([]);
    }
}
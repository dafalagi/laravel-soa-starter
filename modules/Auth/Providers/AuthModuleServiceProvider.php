<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;
use Modules\Auth\Services\Auth\Contracts\LogoutServiceInterface;
use Modules\Auth\Services\Auth\Contracts\RefreshTokenServiceInterface;
use Modules\Auth\Services\Auth\Contracts\RegisterUserServiceInterface;
use Modules\Auth\Services\Auth\LoginService;
use Modules\Auth\Services\Auth\LogoutService;
use Modules\Auth\Services\Auth\RefreshTokenService;
use Modules\Auth\Services\Auth\RegisterUserService;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;
use Modules\Auth\Services\User\GetUserService;

class AuthModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        /** Auth */
        $this->app->bind(RegisterUserServiceInterface::class, RegisterUserService::class);
        $this->app->bind(LoginServiceInterface::class, LoginService::class);
        $this->app->bind(LogoutServiceInterface::class, LogoutService::class);
        $this->app->bind(RefreshTokenServiceInterface::class, RefreshTokenService::class);
        
        /** User */
        $this->app->bind(GetUserServiceInterface::class, GetUserService::class);
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
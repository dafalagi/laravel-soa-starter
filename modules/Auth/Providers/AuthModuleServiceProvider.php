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
        
        // Load module translations
        $this->loadTranslations();
        
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
        $migration_path = __DIR__ . '/../Database/Migrations';
        if (is_dir($migration_path)) {
            $this->loadMigrationsFrom($migration_path);
        }
    }

    /**
     * Load module translations.
     */
    private function loadTranslations(): void
    {
        $translation_path = __DIR__ . '/../Resources/lang';
        if (is_dir($translation_path)) {
            $this->loadTranslationsFrom($translation_path, 'auth');
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
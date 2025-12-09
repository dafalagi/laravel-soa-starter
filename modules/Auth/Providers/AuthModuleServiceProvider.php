<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class AuthModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        /** Auth */
        $this->app->bind(\Modules\Auth\Services\Auth\Contracts\LoginServiceInterface::class, \Modules\Auth\Services\Auth\LoginService::class);
        $this->app->bind(\Modules\Auth\Services\Auth\Contracts\LogoutServiceInterface::class, \Modules\Auth\Services\Auth\LogoutService::class);
        $this->app->bind(\Modules\Auth\Services\Auth\Contracts\RefreshTokenServiceInterface::class, \Modules\Auth\Services\Auth\RefreshTokenService::class);
        
        /** User */
        $this->app->bind(\Modules\Auth\Services\User\Contracts\GetUserServiceInterface::class, \Modules\Auth\Services\User\GetUserService::class);
        $this->app->bind(\Modules\Auth\Services\User\Contracts\StoreUserServiceInterface::class, \Modules\Auth\Services\User\StoreUserService::class);
        $this->app->bind(\Modules\Auth\Services\User\Contracts\UpdateUserServiceInterface::class, \Modules\Auth\Services\User\UpdateUserService::class);
        $this->app->bind(\Modules\Auth\Services\User\Contracts\DeleteUserServiceInterface::class, \Modules\Auth\Services\User\DeleteUserService::class);
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
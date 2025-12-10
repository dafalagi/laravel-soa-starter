<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModuleCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'make:module {name : The name of the module}';

    /**
     * The console command description.
     */
    protected $description = 'Create a new module with all necessary files and folders';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $module_name = Str::studly($this->argument('name'));
        $module_namespace = "Modules\\{$module_name}";

        if (!$this->validateInputs($module_name))
            return self::FAILURE;

        $this->info("Creating module: {$module_name}");

        try {
            // Create module directories
            $module_path = base_path("modules/{$module_name}");
            $this->createDirectories($module_path);

            // Create module files
            $this->createServiceProvider($module_path, $module_name, $module_namespace);
            $this->createRoute($module_path, $module_name);

            $this->info("Module {$module_name} created successfully!");
            $this->info("Don't forget to register the service provider in bootstrap/providers.php:");
            $this->line("{$module_namespace}\\Providers\\{$module_name}ModuleServiceProvider::class,");
        } catch (\Exception $e) {
            $this->error("Failed to create module: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module_name): bool
    {
        if (empty($module_name)) {
            $this->error("Module name cannot be empty.");
            return false;
        }

        $module_path = base_path("modules/{$module_name}");
        if (file_exists($module_path)) {
            $this->error("Module {$module_name} already exists!");
            return false;
        }

        return true;
    }

    private function createDirectories(string $module_path): void
    {
        $directories = [
            'DTOs',
            'Http/Controllers/Api',
            'Models',
            'Providers',
            'Routes',
            'Services',
            'Tests/Feature',
            'Tests/Unit',
            'Database/Migrations',
            'Database/Factories',
            'Database/Seeders',
            'Resources/lang/en',
        ];

        foreach ($directories as $directory) {
            mkdir("{$module_path}/{$directory}", 0755, true);
        }
    }

    private function createServiceProvider(string $module_path, string $module_name, string $module_namespace): void
    {
        $content = $this->getServiceProviderStub($module_name, $module_namespace);
        file_put_contents("{$module_path}/Providers/{$module_name}ModuleServiceProvider.php", $content);
    }

    private function getServiceProviderStub(string $module_name, string $module_namespace): string
    {
        return "<?php

namespace {$module_namespace}\\Providers;

use Illuminate\\Support\\ServiceProvider;

class {$module_name}ModuleServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        /** Some Feature */
        // \$this->app->bind(SomeServiceInterface::class, SomeService::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Load module routes
        \$this->loadRoutes();
        
        // Load module migrations if they exist
        \$this->loadMigrations();
        
        // Load module translations
        \$this->loadTranslations();

        // Register module commands
        if (\$this->app->runningInConsole()) {
            \$this->registerCommands();
        }
    }

    /**
     * Load module routes.
     */
    private function loadRoutes(): void
    {
        if (file_exists(__DIR__ . '/../Routes/api.php')) {
            \$this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        }
    }

    /**
     * Load module migrations.
     */
    private function loadMigrations(): void
    {
        if (is_dir(__DIR__ . '/../Database/Migrations')) {
            \$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        }
    }

    /**
     * Load module translations.
     */
    private function loadTranslations(): void
    {
        \$translationPath = __DIR__ . '/../Resources/lang';
        if (is_dir(\$translationPath)) {
            \$this->loadTranslationsFrom(\$translationPath, '" . strtolower($module_name) . "');
        }
    }

    /**
     * Register module commands.
     */
    private function registerCommands(): void
    {
        // Register any module-specific commands here
        // \$this->commands([]);
    }
}
";
    }

    private function createRoute(string $module_path, string $module_name): void
    {
        $routes_content = $this->getRouteStub($module_name);
        file_put_contents("{$module_path}/Routes/api.php", $routes_content);
    }

    private function getRouteStub(string $module_name): string
    {
        return "<?php

use Illuminate\\Support\\Facades\\Route;

/*|--------------------------------------------------------------------------
| {$module_name} Module API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for the {$module_name} module. These
| routes are loaded by the {$module_name}ModuleServiceProvider within a group which
| is assigned the \"api\" middleware group.
|--------------------------------------------------------------------------*/

Route::prefix('api/v0')->middleware(['api'])->group(function () {
    // require __DIR__ . '/some-directory/some-routes-file.php';
});
";
    }
}
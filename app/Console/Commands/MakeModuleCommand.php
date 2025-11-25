<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
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

    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $moduleName = ucfirst($this->argument('name'));
        $moduleNamespace = "Modules\\{$moduleName}";
        $modulePath = base_path("modules/{$moduleName}");

        if ($this->filesystem->exists($modulePath)) {
            $this->error("Module {$moduleName} already exists!");
            return 1;
        }

        $this->info("Creating module: {$moduleName}");

        // Create module directories
        $this->createDirectories($modulePath);

        // Create module files
        $this->createServiceProvider($modulePath, $moduleName, $moduleNamespace);
        $this->createController($modulePath, $moduleName, $moduleNamespace);
        $this->createService($modulePath, $moduleName, $moduleNamespace);
        $this->createServiceContract($modulePath, $moduleName, $moduleNamespace);
        $this->createDTO($modulePath, $moduleName, $moduleNamespace);
        $this->createRoutes($modulePath, $moduleName);
        $this->createTest($modulePath, $moduleName, $moduleNamespace);

        $this->info("Module {$moduleName} created successfully!");
        $this->info("Don't forget to register the service provider in bootstrap/providers.php:");
        $this->line("{$moduleNamespace}\\Providers\\{$moduleName}ServiceProvider::class,");

        return 0;
    }

    private function createDirectories(string $modulePath): void
    {
        $directories = [
            'DTOs',
            'Http/Controllers',
            'Models',
            'Providers',
            'Routes',
            'Services/Contracts',
            'Tests/Feature',
            'Tests/Unit',
        ];

        foreach ($directories as $directory) {
            $this->filesystem->makeDirectory("{$modulePath}/{$directory}", 0755, true);
        }
    }

    private function createServiceProvider(string $modulePath, string $moduleName, string $moduleNamespace): void
    {
        $content = $this->getServiceProviderStub($moduleName, $moduleNamespace);
        $this->filesystem->put("{$modulePath}/Providers/{$moduleName}ServiceProvider.php", $content);
    }

    private function createController(string $modulePath, string $moduleName, string $moduleNamespace): void
    {
        $content = $this->getControllerStub($moduleName, $moduleNamespace);
        $this->filesystem->put("{$modulePath}/Http/Controllers/{$moduleName}Controller.php", $content);
    }

    private function createService(string $modulePath, string $moduleName, string $moduleNamespace): void
    {
        $content = $this->getServiceStub($moduleName, $moduleNamespace);
        $this->filesystem->put("{$modulePath}/Services/{$moduleName}Service.php", $content);
    }

    private function createServiceContract(string $modulePath, string $moduleName, string $moduleNamespace): void
    {
        $content = $this->getServiceContractStub($moduleName, $moduleNamespace);
        $this->filesystem->put("{$modulePath}/Services/Contracts/{$moduleName}ServiceInterface.php", $content);
    }

    private function createDTO(string $modulePath, string $moduleName, string $moduleNamespace): void
    {
        $content = $this->getDTOStub($moduleName, $moduleNamespace);
        $this->filesystem->put("{$modulePath}/DTOs/{$moduleName}DTO.php", $content);
    }

    private function createRoutes(string $modulePath, string $moduleName): void
    {
        $content = $this->getRoutesStub($moduleName);
        $this->filesystem->put("{$modulePath}/Routes/api.php", $content);
    }

    private function createTest(string $modulePath, string $moduleName, string $moduleNamespace): void
    {
        $content = $this->getTestStub($moduleName, $moduleNamespace);
        $this->filesystem->put("{$modulePath}/Tests/Feature/{$moduleName}ControllerTest.php", $content);
    }

    private function getServiceProviderStub(string $moduleName, string $moduleNamespace): string
    {
        return "<?php

namespace {$moduleNamespace}\\Providers;

use Illuminate\\Support\\ServiceProvider;
use {$moduleNamespace}\\Services\\{$moduleName}Service;
use {$moduleNamespace}\\Services\\Contracts\\{$moduleName}ServiceInterface;

class {$moduleName}ServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register service contracts
        \$this->app->bind({$moduleName}ServiceInterface::class, {$moduleName}Service::class);
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
}
";
    }

    private function getControllerStub(string $moduleName, string $moduleNamespace): string
    {
        $routePrefix = Str::kebab($moduleName);
        return "<?php

namespace {$moduleNamespace}\\Http\\Controllers;

use App\\Http\\Controllers\\Controller;
use App\\Http\\Traits\\ApiResponse;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;
use {$moduleNamespace}\\Services\\Contracts\\{$moduleName}ServiceInterface;

class {$moduleName}Controller extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly {$moduleName}ServiceInterface \${$routePrefix}Service
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        // TODO: Implement index method
        return \$this->successResponse('List retrieved successfully', []);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request \$request): JsonResponse
    {
        // TODO: Implement store method
        return \$this->successResponse('Resource created successfully', [], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string \$id): JsonResponse
    {
        // TODO: Implement show method
        return \$this->successResponse('Resource retrieved successfully', []);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request \$request, string \$id): JsonResponse
    {
        // TODO: Implement update method
        return \$this->successResponse('Resource updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string \$id): JsonResponse
    {
        // TODO: Implement destroy method
        return \$this->successResponse('Resource deleted successfully');
    }
}
";
    }

    private function getServiceStub(string $moduleName, string $moduleNamespace): string
    {
        return "<?php

namespace {$moduleNamespace}\\Services;

use {$moduleNamespace}\\Services\\Contracts\\{$moduleName}ServiceInterface;

class {$moduleName}Service implements {$moduleName}ServiceInterface
{
    /**
     * Get all items.
     */
    public function getAll(): array
    {
        // TODO: Implement business logic
        return [];
    }

    /**
     * Get item by ID.
     */
    public function getById(int \$id): ?array
    {
        // TODO: Implement business logic
        return null;
    }

    /**
     * Create new item.
     */
    public function create(array \$data): array
    {
        // TODO: Implement business logic
        return [];
    }

    /**
     * Update item.
     */
    public function update(int \$id, array \$data): bool
    {
        // TODO: Implement business logic
        return true;
    }

    /**
     * Delete item.
     */
    public function delete(int \$id): bool
    {
        // TODO: Implement business logic
        return true;
    }
}
";
    }

    private function getServiceContractStub(string $moduleName, string $moduleNamespace): string
    {
        return "<?php

namespace {$moduleNamespace}\\Services\\Contracts;

interface {$moduleName}ServiceInterface
{
    /**
     * Get all items.
     */
    public function getAll(): array;

    /**
     * Get item by ID.
     */
    public function getById(int \$id): ?array;

    /**
     * Create new item.
     */
    public function create(array \$data): array;

    /**
     * Update item.
     */
    public function update(int \$id, array \$data): bool;

    /**
     * Delete item.
     */
    public function delete(int \$id): bool;
}
";
    }

    private function getDTOStub(string $moduleName, string $moduleNamespace): string
    {
        return "<?php

namespace {$moduleNamespace}\\DTOs;

class {$moduleName}DTO
{
    public function __construct(
        // TODO: Add properties
    ) {}

    public static function fromArray(array \$data): self
    {
        return new self(
            // TODO: Map array data to properties
        );
    }

    public function toArray(): array
    {
        return [
            // TODO: Map properties to array
        ];
    }
}
";
    }

    private function getRoutesStub(string $moduleName): string
    {
        $routePrefix = Str::kebab($moduleName);
        $controllerName = "{$moduleName}Controller";
        
        return "<?php

use Illuminate\\Support\\Facades\\Route;
use Modules\\{$moduleName}\\Http\\Controllers\\{$controllerName};

/*
|--------------------------------------------------------------------------
| {$moduleName} Module API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for the {$moduleName} module. These
| routes are loaded by the {$moduleName}ServiceProvider within a group which
| is assigned the \"api\" middleware group.
|
*/

Route::prefix('api/v1/{$routePrefix}')->middleware(['api'])->group(function () {
    Route::get('/', [{$controllerName}::class, 'index'])->name('{$routePrefix}.index');
    Route::post('/', [{$controllerName}::class, 'store'])->name('{$routePrefix}.store');
    Route::get('/{id}', [{$controllerName}::class, 'show'])->name('{$routePrefix}.show');
    Route::put('/{id}', [{$controllerName}::class, 'update'])->name('{$routePrefix}.update');
    Route::delete('/{id}', [{$controllerName}::class, 'destroy'])->name('{$routePrefix}.destroy');
});
";
    }

    private function getTestStub(string $moduleName, string $moduleNamespace): string
    {
        $routePrefix = Str::kebab($moduleName);
        
        return "<?php

namespace {$moduleNamespace}\\Tests\\Feature;

use Illuminate\\Foundation\\Testing\\RefreshDatabase;
use Tests\\TestCase;

class {$moduleName}ControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_list_{$routePrefix}(): void
    {
        \$response = \$this->getJson('/api/v1/{$routePrefix}');

        \$response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_can_create_{$routePrefix}(): void
    {
        \$data = [
            // TODO: Add test data
        ];

        \$response = \$this->postJson('/api/v1/{$routePrefix}', \$data);

        \$response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_can_show_{$routePrefix}(): void
    {
        // TODO: Create test data
        \$id = 1;

        \$response = \$this->getJson(\"/api/v1/{$routePrefix}/{\$id}\");

        \$response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_can_update_{$routePrefix}(): void
    {
        // TODO: Create test data
        \$id = 1;
        \$data = [
            // TODO: Add update data
        ];

        \$response = \$this->putJson(\"/api/v1/{$routePrefix}/{\$id}\", \$data);

        \$response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }

    public function test_can_delete_{$routePrefix}(): void
    {
        // TODO: Create test data
        \$id = 1;

        \$response = \$this->deleteJson(\"/api/v1/{$routePrefix}/{\$id}\");

        \$response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
            ]);
    }
}
";
    }
}
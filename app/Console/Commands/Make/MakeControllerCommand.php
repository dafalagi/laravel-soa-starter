<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeControllerCommand extends Command
{
    protected $signature = 'make:controller 
                           {module : The module name}
                           {controller : The controller name}
                           {client : The client name (Web/Admin/Mobile)}';

    protected $description = 'Create a new controller in a specific module';

    public function handle(): int
    {
        $module_name = Str::studly($this->argument('module'));
        $controller_name = Str::studly($this->argument('controller'));
        $client = Str::studly($this->argument('client'));

        if (!$this->validateInputs($module_name, $controller_name, $client))
            return self::FAILURE;

        // Ensure controller name ends with 'Controller'
        if (Str::contains($controller_name, 'controller')) {
            $controller_name = Str::replaceLast('controller', 'Controller', $controller_name);
        } else if (!Str::endsWith($controller_name, 'Controller')) {
            $controller_name .= 'Controller';
        }

        $module_path = base_path("modules/{$module_name}");
        $controller_path = "{$module_path}/Http/Controllers/Api/{$client}/{$controller_name}.php";

        // Check if controller already exists
        if (file_exists($controller_path)) {
            $this->error("Controller {$controller_name} already exists in {$module_name} module!");
            return self::FAILURE;
        }

        try {
            // Generate controller content
            $module_namespace = "Modules\\{$module_name}";
            $client_namespace = "Api\\{$client}";
            $content = $this->getControllerStub(
                $controller_name, 
                $module_namespace,
                $client_namespace,
            );

            // Create controller file
            file_put_contents($controller_path, $content);

            $this->info("Controller {$controller_name} created successfully in {$module_name} module!");
        } catch (\Exception $e) {
            $this->error("Failed to create controller: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $controller, string $client): bool
    {
        if (empty($module) || empty($controller) || empty($client)) {
            $this->error('Module, controller, and client are required.');
            return false;
        }

        $module_path = base_path("modules/{$module}");
        if (!is_dir($module_path)) {
            $this->error("Module {$module} does not exist!");
            return false;
        }

        $valid_clients = ['Web', 'Admin', 'Mobile'];
        if (!in_array($client, $valid_clients)) {
            $this->error("Client must be one of: " . implode(', ', $valid_clients));
            return false;
        }

        return true;
    }

    private function getControllerStub(
        string $controller_name, 
        string $module_namespace, 
        string $client_namespace,
    ): string {
        $base_controller = 'ApiController';
        $constructor = '';
        $constructor = "\n    public function __construct()\n    {\n    }";

        $methods = "
    public function index(\$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function show(\$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function store(\$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function update(\$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function destroy(\$request): JsonResponse
    {
        return \$this->response([]);
    }";

        return "<?php

namespace {$module_namespace}\\Http\\Controllers\\{$client_namespace};

use App\\Http\\Controllers\\{$base_controller};
use Illuminate\\Http\\JsonResponse;

class {$controller_name} extends {$base_controller}
{{$constructor}
{$methods}
}
";
    }
}
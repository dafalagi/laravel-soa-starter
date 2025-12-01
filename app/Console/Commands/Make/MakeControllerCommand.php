<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeControllerCommand extends Command
{
    protected $signature = 'make:controller 
                           {module : The module name}
                           {feature : The feature name}
                           {controller : The controller name}
                           {client : The client name (Web/Admin/Mobile)}';

    protected $description = 'Create a new controller in a specific module';

    private Filesystem $filesystem;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    public function handle(): int
    {
        $module_name = Str::studly($this->argument('module'));
        $feature_name = Str::studly($this->argument('feature'));
        $controller_name = Str::studly($this->argument('controller'));
        $client = Str::studly($this->argument('client'));

        // Validate module exists
        $module_path = base_path("modules/{$module_name}");
        if (!$this->filesystem->isDirectory($module_path)) {
            $this->error("Module {$module_name} does not exist!");
            return 1;
        }

        // Ensure controller name ends with 'Controller'
        if (Str::contains($controller_name, 'controller')) {
            $controller_name = Str::replaceLast('controller', 'Controller', $controller_name);
        } else if (!Str::endsWith($controller_name, 'Controller')) {
            $controller_name .= 'Controller';
        }

        // Ensure client argument is valid
        $valid_clients = ['Web', 'Admin', 'Mobile'];
        if (!in_array($client, $valid_clients)) {
            $this->error("Client must be one of: " . implode(', ', $valid_clients));
            return 1;
        }

        // Ensure feature directory exists
        $feat_path = "{$module_path}/Http/Controllers/Api/{$client}/{$feature_name}";
        if (!$this->filesystem->isDirectory($feat_path))
            $this->filesystem->makeDirectory($feat_path, 0755, true);

        $controller_path = "{$module_path}/Http/Controllers/Api/{$client}/{$feature_name}/{$controller_name}.php";

        // Check if controller already exists
        if ($this->filesystem->exists($controller_path)) {
            $this->error("Controller {$controller_name} already exists in {$module_name} module!");
            return 1;
        }

        // Generate controller content
        $module_namespace = "Modules\\{$module_name}";
        $feature_namespace = "Api\\{$client}\\{$feature_name}";
        $content = $this->getControllerStub(
            $controller_name, 
            $module_namespace,
            $feature_namespace,
        );

        // Create controller file
        $this->filesystem->put($controller_path, $content);

        $this->info("Controller {$controller_name} created successfully in {$module_name} module!");

        return 0;
    }

    private function getControllerStub(
        string $controller_name, 
        string $module_namespace, 
        string $feature_namespace,
    ): string {
        $base_controller = 'ApiController';
        $constructor = '';
        $constructor = "\n    public function __construct()\n    {\n    }";

        $methods = "
    public function index(Request \$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function show(Request \$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function store(Request \$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function update(Request \$request): JsonResponse
    {
        return \$this->response([]);
    }

    public function destroy(Request \$request): JsonResponse
    {
        return \$this->response([]);
    }";

        return "<?php

namespace {$module_namespace}\\Http\\Controllers\\{$feature_namespace};

use App\\Http\\Controllers\\{$base_controller};
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Http\\Request;

class {$controller_name} extends {$base_controller}
{{$constructor}
{$methods}
}
";
    }
}
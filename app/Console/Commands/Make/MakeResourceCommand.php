<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeResourceCommand extends Command
{
    protected $signature = 'make:resource 
                           {module : The module name}
                           {feature : The feature name}
                           {resource : The resource name}
                           {client : The client name (Web/Admin/Mobile)}';

    protected $description = 'Create a new JSON resource in a specific module';

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
        $resource_name = Str::studly($this->argument('resource'));
        $client = Str::studly($this->argument('client'));

        // Validate module exists
        $module_path = base_path("modules/{$module_name}");
        if (!$this->filesystem->isDirectory($module_path)) {
            $this->error("Module {$module_name} does not exist!");
            return 1;
        }

        // Ensure resource name ends with 'Resource'
        if (Str::contains($resource_name, 'resource')) {
            $resource_name = Str::replaceLast('resource', 'Resource', $resource_name);
        } else if (!Str::endsWith($resource_name, 'Resource')) {
            $resource_name .= 'Resource';
        }

        // Ensure client argument is valid
        $valid_clients = ['Web', 'Admin', 'Mobile'];
        if (!in_array($client, $valid_clients)) {
            $this->error("Client must be one of: " . implode(', ', $valid_clients));
            return 1;
        }

        // Ensure feature directory exists
        $resources_path = "{$module_path}/Http/Resources/Api/{$client}/{$feature_name}";
        if (!$this->filesystem->isDirectory($resources_path)) {
            $this->filesystem->makeDirectory($resources_path, 0755, true);
        }

        $resource_path = "{$module_path}/Http/Resources/Api/{$client}/{$feature_name}/{$resource_name}.php";

        // Check if resource already exists
        if ($this->filesystem->exists($resource_path)) {
            $this->error("Resource {$resource_name} already exists in {$module_name} module!");
            return 1;
        }

        // Generate resource content
        $module_namespace = "Modules\\{$module_name}";
        $feature_namespace = "Api\\{$client}\\{$feature_name}";
        $content = $this->getResourceStub(
            $resource_name,
            $module_namespace,
            $feature_namespace
        );

        // Create resource file
        $this->filesystem->put($resource_path, $content);

        $this->info("Resource {$resource_name} created successfully in {$module_name} module!");

        return 0;
    }

    private function getResourceStub(
        string $resource_name,
        string $module_namespace,
        string $feature_namespace
    ): string {
        return "<?php

namespace {$module_namespace}\\Http\\Resources\\{$feature_namespace};

use Illuminate\\Http\\Request;
use Illuminate\\Http\\Resources\\Json\\JsonResource;

class {$resource_name} extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request \$request): array
    {
        return [
            'uuid' => \$this->uuid,
            'created_at' => \$this->created_at,
            'updated_at' => \$this->updated_at,
        ];
    }
}
";
    }
}
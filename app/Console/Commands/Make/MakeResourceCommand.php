<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeResourceCommand extends Command
{
    protected $signature = 'make:resource 
                           {module : The module name}
                           {feature : The feature name}
                           {resource : The resource name}
                           {client : The client name (Web/Admin/Mobile)}';

    protected $description = 'Create a new JSON resource in a specific module';

    public function handle(): int
    {
        $module_name = Str::studly($this->argument('module'));
        $feature_name = Str::studly($this->argument('feature'));
        $resource_name = Str::studly($this->argument('resource'));
        $client = Str::studly($this->argument('client'));

        if (!$this->validateInputs($module_name, $feature_name, $resource_name, $client))
            return self::FAILURE;

        // Ensure resource name ends with 'Resource'
        if (Str::contains($resource_name, 'resource')) {
            $resource_name = Str::replaceLast('resource', 'Resource', $resource_name);
        } else if (!Str::endsWith($resource_name, 'Resource')) {
            $resource_name .= 'Resource';
        }

        // Ensure feature directory exists
        $module_path = base_path("modules/{$module_name}");
        $resources_path = "{$module_path}/Http/Resources/Api/{$client}/{$feature_name}";
        if (!is_dir($resources_path))
            mkdir($resources_path, 0755, true);

        $resource_path = "{$resources_path}/{$resource_name}.php";
        if (file_exists($resource_path)) {
            $this->error("Resource {$resource_name} already exists in {$module_name} module!");
            return self::FAILURE;
        }

        try {
            // Generate resource content
            $module_namespace = "Modules\\{$module_name}";
            $feature_namespace = "Api\\{$client}\\{$feature_name}";
            $content = $this->getResourceStub(
                $resource_name,
                $module_namespace,
                $feature_namespace
            );

            // Create resource file
            file_put_contents($resource_path, $content);

            $this->info("Resource {$resource_name} created successfully in {$module_name} module!");
        } catch (\Exception $e) {
            $this->error("Failed to create resource: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $feature, string $resource, string $client): bool
    {
        if (empty($module) || empty($feature) || empty($resource)) {
            $this->error('Module, feature, and resource name are required.');
            return false;
        }

        $module_path = base_path("modules/{$module}");
        if (!is_dir($module_path)) {
            $this->error("Module '{$module}' does not exist. Please create the module first.");
            return false;
        }

        // Ensure client argument is valid
        $valid_clients = ['Web', 'Admin', 'Mobile'];
        if (!in_array($client, $valid_clients)) {
            $this->error("Client must be one of: " . implode(', ', $valid_clients));
            return false;
        }

        return true;
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
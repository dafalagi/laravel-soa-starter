<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeServiceCommand extends Command
{
    protected $signature = 'make:service 
                            {module : The module name} 
                            {feature : The feature name}
                            {service : The service name}
                            {--force}';

    protected $description = 'Create a new service class with its interface';

    public function handle(): int
    {
        $module = $this->argument('module');
        $feature = $this->argument('feature');
        $service = $this->argument('service');
        $force = $this->option('force');

        // Validate inputs
        if (!$this->validateInputs($module, $feature, $service)) {
            return self::FAILURE;
        }

        $service_name = Str::studly($service);
        $feature_name = Str::studly($feature);
        $module_name = Str::studly($module);

        // Ensure service name ends with 'Service'
        if (!Str::endsWith($service_name, 'Service'))
            $service_name .= 'Service';

        // Generate interface name
        $interface_name = $service_name . 'Interface';

        try {
            // Create service interface
            $this->createServiceInterface($module_name, $feature_name, $service_name, $interface_name, $force);
            
            // Create service class
            $this->createServiceClass($module_name, $feature_name, $service_name, $interface_name, $force);

            $this->info("Service created successfully!");
            $this->info("Interface: modules/{$module_name}/Services/{$feature_name}/Contracts/{$interface_name}.php");
            $this->info("Service: modules/{$module_name}/Services/{$feature_name}/{$service_name}.php");

        } catch (\Exception $e) {
            $this->error("Failed to create service: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $feature, string $service): bool
    {
        if (empty($module) || empty($feature) || empty($service)) {
            $this->error('Module, feature, and service name are required.');
            return false;
        }

        $module_path = base_path("modules/" . Str::studly($module));
        if (!is_dir($module_path)) {
            $this->error("Module '{$module}' does not exist. Please create the module first.");
            return false;
        }

        return true;
    }

    private function createServiceInterface(string $module, string $feature, string $service_name, string $interface_name, bool $force): void
    {
        $interface_path = base_path("modules/{$module}/Services/{$feature}/Contracts/{$interface_name}.php");
        
        if (file_exists($interface_path) && !$force) {
            $this->error("Interface {$interface_name} already exists. Use --force to overwrite.");
            throw new \Exception("Interface already exists");
        }

        $interface_dir = dirname($interface_path);
        if (!is_dir($interface_dir))
            mkdir($interface_dir, 0755, true);

        $interface_stub = $this->getInterfaceStub($module, $feature, $service_name, $interface_name);
        file_put_contents($interface_path, $interface_stub);
    }

    private function createServiceClass(string $module, string $feature, string $service_name, string $interface_name, bool $force): void
    {
        $service_path = base_path("modules/{$module}/Services/{$feature}/{$service_name}.php");
        
        if (file_exists($service_path) && !$force) {
            $this->error("Service {$service_name} already exists. Use --force to overwrite.");
            throw new \Exception("Service already exists");
        }

        $service_dir = dirname($service_path);
        if (!is_dir($service_dir))
            mkdir($service_dir, 0755, true);

        $service_stub = $this->getServiceStub($module, $feature, $service_name, $interface_name);
        file_put_contents($service_path, $service_stub);
    }

    private function getInterfaceStub(string $module, string $feature, string $service_name, string $interface_name): string
    {
        return "<?php

namespace Modules\\{$module}\\Services\\{$feature}\\Contracts;

interface {$interface_name}
{
    /**
     * Execute the {$service_name} operation.
     * 
     * TODO: Define the appropriate DTO instead of mixed.
     */
    public function execute(mixed \$dto, bool \$sub_service = false): array;
}
";
    }

    private function getServiceStub(string $module, string $feature, string $service_name, string $interface_name): string
    {
        return "<?php

namespace Modules\\{$module}\\Services\\{$feature};

use App\\Services\\BaseService;
use Modules\\{$module}\\Services\\{$feature}\\Contracts\\{$interface_name};

class {$service_name} extends BaseService implements {$interface_name}
{
    public function execute(mixed \$dto, bool \$sub_service = false): array
    {
        return parent::execute(\$dto->toArray(), \$sub_service);
    }

    protected function process(mixed \$dto): void
    {
        \$dto = \$this->prepare(\$dto);

        // TODO: Implement the service logic here

        \$this->results['data'] = []; // Replace with actual data, e.g., UserResponseDTO::fromModel(\$model);
        \$this->results['message'] = __(''); // Add appropriate success message;
    }

    private function prepare(array \$dto): array
    {
        // TODO: Prepare data before processing (e.g., hash passwords, format data)
        
        return \$dto;
    }

    protected function rules(array \$dto): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}
";
    }
}
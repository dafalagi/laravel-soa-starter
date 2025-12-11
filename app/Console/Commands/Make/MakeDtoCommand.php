<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto 
                           {module : The module name}
                           {feature : The feature name}
                           {name : The DTO name}
                           {type : The DTO type (request/response)}';

    protected $description = 'Create a new DTO in a specific module';

    public function handle(): int
    {
        $module_name = Str::studly($this->argument('module'));
        $feature_name = Str::studly($this->argument('feature'));
        $dto_name = Str::studly($this->argument('name'));
        $dto_type = strtolower($this->argument('type'));

        if (!$this->validateInputs($module_name, $feature_name, $dto_name, $dto_type))
            return self::FAILURE;

        // Ensure DTO name ends with 'RequestDTO' or 'ResponseDTO'
        $type_suffix = $dto_type === 'request' ? 'RequestDTO' : 'ResponseDTO';
        if (!Str::endsWith($dto_name, $type_suffix)) {
            $dto_name .= $type_suffix;
        }

        // Determine directory based on type
        $module_path = base_path("modules/{$module_name}");
        $type_folder = Str::studly($dto_type) . 's'; // Requests or Responses
        $dto_path_dir = "{$module_path}/DTOs/{$feature_name}/{$type_folder}";
        
        // Ensure directory exists
        if (!is_dir($dto_path_dir)) {
            mkdir($dto_path_dir, 0755, true);
        }

        $dto_path = "{$dto_path_dir}/{$dto_name}.php";

        // Check if DTO already exists
        if (file_exists($dto_path)) {
            $this->error("DTO {$dto_name} already exists in {$module_name} module!");
            return self::FAILURE;
        }

        try {
            // Generate DTO content
            $module_namespace = "Modules\\{$module_name}";
            $content = $this->getDtoStub($dto_name, $module_namespace, $feature_name, $dto_type);

            // Create DTO file
            file_put_contents($dto_path, $content);

            $this->info("DTO {$dto_name} created successfully in {$module_name} module!");
        } catch (\Exception $e) {
            $this->error("Failed to create DTO: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $feature, string $dto, string $type): bool
    {
        if (empty($module) || empty($feature) || empty($dto) || empty($type)) {
            $this->error('Module, feature, DTO name, and type are required.');
            return false;
        }

        $module_path = base_path("modules/{$module}");
        if (!is_dir($module_path)) {
            $this->error("Module '{$module}' does not exist. Please create the module first.");
            return false;
        }

        $valid_types = ['request', 'response'];
        if (!in_array($type, $valid_types)) {
            $this->error("DTO type must be one of: " . implode(', ', $valid_types));
            return false;
        }

        return true;
    }

    private function getDtoStub(string $dto_name, string $module_namespace, string $feature_name, string $dto_type): string
    {
        $type_folder = Str::studly($dto_type) . 's';
        
        if ($dto_type === 'request') {
            return $this->getRequestDtoStub($dto_name, $module_namespace, $feature_name, $type_folder);
        } else {
            return $this->getResponseDtoStub($dto_name, $module_namespace, $feature_name, $type_folder);
        }
    }

    private function getRequestDtoStub(string $dto_name, string $module_namespace, string $feature_name, string $type_folder): string
    {
        return "<?php

namespace {$module_namespace}\\DTOs\\{$feature_name}\\{$type_folder};

class {$dto_name}
{
    public function __construct(
        // TODO: Add your properties here
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
            // TODO: Return array representation
        ];
    }
}
";
    }

    private function getResponseDtoStub(string $dto_name, string $module_namespace, string $feature_name, string $type_folder): string
    {
        $model_name = $feature_name; // Assuming model name matches feature name
        
        return "<?php

namespace {$module_namespace}\\DTOs\\{$feature_name}\\{$type_folder};

use Illuminate\\Support\\Collection;
use {$module_namespace}\\Models\\{$model_name};

class {$dto_name}
{
    public function __construct(
        // TODO: Add your properties here
    ) {}

    public static function fromModel({$model_name} \$model): self
    {
        return new self(
            // TODO: Map model data to properties
        );
    }

    public static function fromCollection(Collection \$models): array
    {
        return array_map(fn({$model_name} \$model) => self::fromModel(\$model), \$models->all());
    }

    /**
     * @param array<string>|null \$only Only these fields will be included in the output array
     * @param array<string>|null \$except These fields will be excluded from the output array
     */
    public function toArray(?array \$only = null, ?array \$except = null): array
    {
        \$data = [];

        if (\$only)
            \$data = array_intersect_key(\$data, array_flip(\$only));

        if (\$except)
            \$data = array_diff_key(\$data, array_flip(\$except));

        return \$data;
    }
}
";
    }
}
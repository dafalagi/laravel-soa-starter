<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeDtoCommand extends Command
{
    protected $signature = 'make:dto 
                           {module : The module name}
                           {feature : The feature name}
                           {name : The DTO name}
                           {type : The DTO type (request/response)}';

    protected $description = 'Create a new DTO in a specific module';

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
        $dto_name = Str::studly($this->argument('name'));
        $dto_type = strtolower($this->argument('type'));

        // Validate module exists
        $module_path = base_path("modules/{$module_name}");
        if (!$this->filesystem->isDirectory($module_path)) {
            $this->error("Module {$module_name} does not exist!");
            return 1;
        }

        // Validate DTO type
        $valid_types = ['request', 'response'];
        if (!in_array($dto_type, $valid_types)) {
            $this->error("DTO type must be one of: " . implode(', ', $valid_types));
            return 1;
        }

        // Ensure DTO name ends with 'RequestDTO' or 'ResponseDTO'
        $type_suffix = $dto_type === 'request' ? 'RequestDTO' : 'ResponseDTO';
        if (!Str::endsWith($dto_name, $type_suffix)) {
            $dto_name .= $type_suffix;
        }

        // Determine directory based on type
        $type_folder = Str::studly($dto_type) . 's'; // Requests or Responses
        $dto_path_dir = "{$module_path}/DTOs/{$feature_name}/{$type_folder}";
        
        // Ensure directory exists
        if (!$this->filesystem->isDirectory($dto_path_dir)) {
            $this->filesystem->makeDirectory($dto_path_dir, 0755, true);
        }

        $dto_path = "{$dto_path_dir}/{$dto_name}.php";

        // Check if DTO already exists
        if ($this->filesystem->exists($dto_path)) {
            $this->error("DTO {$dto_name} already exists in {$module_name} module!");
            return 1;
        }

        // Generate DTO content
        $module_namespace = "Modules\\{$module_name}";
        $content = $this->getDtoStub($dto_name, $module_namespace, $feature_name, $dto_type);

        // Create DTO file
        $this->filesystem->put($dto_path, $content);

        $this->info("DTO {$dto_name} created successfully in {$module_name} module!");

        return 0;
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

    public function toArray(): array
    {
        return [
            // TODO: Return array representation
        ];
    }
}
";
    }
}
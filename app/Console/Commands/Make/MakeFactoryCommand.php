<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeFactoryCommand extends Command
{
    protected $signature = 'make:factory 
                            {module : The module name} 
                            {model : The model name}
                            {--force}';

    protected $description = 'Create a new factory class for the specified model';

    public function handle(): int
    {
        $module = $this->argument('module');
        $model = $this->argument('model');
        $force = $this->option('force');

        // Validate inputs
        if (!$this->validateInputs($module, $model))
            return self::FAILURE;

        $model_name = Str::studly($model);
        $module_name = Str::studly($module);
        $factory_name = $model_name . 'Factory';

        try {
            // Create factory class
            $this->createFactoryClass($module_name, $model_name, $factory_name, $force);

            $this->info("Factory created successfully!");
            $this->info("Factory: modules/{$module_name}/Database/Factories/{$factory_name}.php");

        } catch (\Exception $e) {
            $this->error("Failed to create factory: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $model): bool
    {
        if (empty($module) || empty($model)) {
            $this->error('Module and model name are required.');
            return false;
        }

        $module_path = base_path("modules/" . Str::studly($module));
        if (!is_dir($module_path)) {
            $this->error("Module '{$module}' does not exist. Please create the module first.");
            return false;
        }

        // Check if model exists
        $model_path = base_path("modules/" . Str::studly($module) . "/Models/" . Str::studly($model) . ".php");
        if (!file_exists($model_path)) {
            $this->warn("Model '{$model}' does not exist in module '{$module}'. Make sure to create the model first.");
        }

        return true;
    }

    private function createFactoryClass(string $module, string $model_name, string $factory_name, bool $force): void
    {
        $factory_path = base_path("modules/{$module}/Database/Factories/{$factory_name}.php");
        
        if (file_exists($factory_path) && !$force) {
            $this->error("Factory {$factory_name} already exists. Use --force to overwrite.");
            throw new \Exception("Factory already exists");
        }

        $factory_dir = dirname($factory_path);
        if (!is_dir($factory_dir))
            mkdir($factory_dir, 0755, true);

        $factory_stub = $this->getFactoryStub($module, $model_name, $factory_name);
        file_put_contents($factory_path, $factory_stub);
    }

    private function getFactoryStub(string $module, string $model_name, string $factory_name): string
    {
        return "<?php

namespace Modules\\{$module}\\Database\\Factories;

use Illuminate\\Database\\Eloquent\\Factories\\Factory;
use Modules\\{$module}\\Models\\{$model_name};

/**
 * @extends Factory<{$model_name}>
 */
class {$factory_name} extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\\Illuminate\\Database\\Eloquent\\Model>
     */
    protected \$model = {$model_name}::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),

            // TODO: Add your model's factory attributes here
            
            'is_active' => true,
            'version' => 0,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'created_at' => time(),
            'updated_at' => null,
            'deleted_at' => null,
        ];
    }

    // TODO: Add additional state modifiers as needed
}
";
    }
}
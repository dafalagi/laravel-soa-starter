<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeModelCommand extends Command
{
    protected $signature = 'make:model 
                            {module : The module name} 
                            {model : The model name}
                            {--force}
                            {--m|migration : Create a migration file for the model}';

    protected $description = 'Create a new model class that extends BaseModel';

    public function handle(): int
    {
        $module = $this->argument('module');
        $model = $this->argument('model');
        $force = $this->option('force');
        $create_migration = $this->option('migration');

        // Validate inputs
        if (!$this->validateInputs($module, $model))
            return self::FAILURE;

        $model_name = Str::studly($model);
        $module_name = Str::studly($module);

        try {
            // Create model class
            $this->createModelClass($module_name, $model_name, $force);

            $this->info("Model created successfully!");
            $this->info("Model: modules/{$module_name}/Models/{$model_name}.php");

            if ($create_migration) {
                // Reuse MakeMigrationCommand to create migration
                $table_name = $this->getTableName($module_name, $model_name);
                $migration_name = "create_" . $table_name . "_table";
                
                $this->call('make:migration', [
                    'module' => $module_name,
                    'name' => $migration_name,
                    '--create' => $table_name,
                    '--force' => $force,
                ]);
            }

        } catch (\Exception $e) {
            $this->error("Failed to create model: " . $e->getMessage());
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

        return true;
    }

    private function createModelClass(string $module, string $model_name, bool $force): void
    {
        $model_path = base_path("modules/{$module}/Models/{$model_name}.php");
        
        if (file_exists($model_path) && !$force) {
            $this->error("Model {$model_name} already exists. Use --force to overwrite.");
            throw new \Exception("Model already exists");
        }

        $model_dir = dirname($model_path);
        if (!is_dir($model_dir))
            mkdir($model_dir, 0755, true);

        $model_stub = $this->getModelStub($module, $model_name);
        file_put_contents($model_path, $model_stub);
    }

    private function getModelStub(string $module, string $model_name): string
    {
        $table_name = $this->getTableName($module, $model_name);
        
        return "<?php

namespace Modules\\{$module}\\Models;

use App\\Models\\BaseModel;

class {$model_name} extends BaseModel
{
    protected \$table = '{$table_name}';

    /**
     * Get the relations that should restrict deletion.
     */
    private function getRestrictOnDeleteRelations(): array
    {
        return [];
    }

    // TODO: Add relationships, scopes, and other model methods
}
";
    }

    private function getTableName(string $module, string $model_name): string
    {
        // Convert to snake_case and pluralize, with module prefix
        $module_snake = Str::snake($module);
        $model_snake = Str::snake($model_name);
        $pluralized = Str::plural($model_snake);
        
        return "{$module_snake}_{$pluralized}";
    }
}
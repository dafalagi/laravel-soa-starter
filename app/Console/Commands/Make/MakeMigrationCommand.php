<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeMigrationCommand extends Command
{
    protected $signature = 'make:migration 
                            {module : The module name} 
                            {name : The migration name}
                            {--create= : The table to be created}
                            {--table= : The table to migrate}
                            {--force}';

    protected $description = 'Create a new migration file in the specified module';

    public function handle(): int
    {
        $module = $this->argument('module');
        $name = $this->argument('name');
        $force = $this->option('force');
        $create = $this->option('create');
        $table = $this->option('table');

        // Validate inputs
        if (!$this->validateInputs($module, $name))
            return self::FAILURE;

        $migration_name = Str::snake($name);
        $module_name = Str::studly($module);

        try {
            // Create migration file
            $this->createMigrationFile($module_name, $migration_name, $create, $table, $force);

            $this->info("Migration created successfully!");
            $this->info("Migration: modules/{$module_name}/Database/Migrations/{$this->getMigrationFileName($migration_name)}.php");

        } catch (\Exception $e) {
            $this->error("Failed to create migration: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $name): bool
    {
        if (empty($module) || empty($name)) {
            $this->error('Module and migration name are required.');
            return false;
        }

        $module_path = base_path("modules/" . Str::studly($module));
        if (!is_dir($module_path)) {
            $this->error("Module '{$module}' does not exist. Please create the module first.");
            return false;
        }

        return true;
    }

    private function createMigrationFile(string $module, string $migration_name, ?string $create, ?string $table, bool $force): void
    {
        $migration_filename = $this->getMigrationFileName($migration_name);
        $migration_path = base_path("modules/{$module}/Database/Migrations/{$migration_filename}.php");
        
        if (file_exists($migration_path) && !$force) {
            $this->error("Migration {$migration_filename} already exists. Use --force to overwrite.");
            throw new \Exception("Migration already exists");
        }

        $migration_dir = dirname($migration_path);
        if (!is_dir($migration_dir))
            mkdir($migration_dir, 0755, true);

        $migration_stub = $this->getMigrationStub($create, $table);
        file_put_contents($migration_path, $migration_stub);
    }

    private function getMigrationFileName(string $migration_name): string
    {
        // Generate timestamp: YYYY_MM_DD_HHMMSS
        $timestamp = now()->format('Y_m_d_His');
        
        // Get next sequence number for today
        $sequence = $this->getNextSequenceNumber();
        
        return "{$timestamp}_{$sequence}_{$migration_name}";
    }

    private function getNextSequenceNumber(): string
    {
        // Find existing migrations for today and get next sequence
        $today = now()->format('Y_m_d');
        $pattern = base_path("modules/*/Database/Migrations/{$today}_*.php");
        $existing = glob($pattern);
        
        $highest = 0;
        foreach ($existing as $file) {
            // Match pattern: YYYY_MM_DD_HHMMSS_sequence_name.php
            if (preg_match("/{$today}_\d{6}_(\d{6})/", basename($file), $matches)) {
                $highest = max($highest, (int)$matches[1]);
            }
        }
        
        return str_pad($highest + 1, 6, '0', STR_PAD_LEFT);
    }

    private function getMigrationStub(?string $create, ?string $table): string
    {
        if ($create) {
            return $this->getCreateTableStub($create);
        } elseif ($table) {
            return $this->getModifyTableStub($table);
        } else {
            return $this->getBlankMigrationStub();
        }
    }

    private function getCreateTableStub(string $table_name): string
    {
        return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('{$table_name}', function (Blueprint \$table) {
            \$table->id();
            \$table->uuid()->unique();

            // TODO: Add your table columns here
            
            \$table->boolean('is_active')->default(true);
            \$table->integer('version')->default(0);
            \$table->integer('created_by')->nullable();
            \$table->integer('updated_by')->nullable();
            \$table->integer('deleted_by')->nullable();
            \$table->integer('created_at')->nullable();
            \$table->integer('updated_at')->nullable();
            \$table->integer('deleted_at')->nullable();

            \$table->index([
                'id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('{$table_name}');
    }
};
";
    }

    private function getModifyTableStub(string $table_name): string
    {
        return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('{$table_name}', function (Blueprint \$table) {
            // TODO: Add your table modifications here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('{$table_name}', function (Blueprint \$table) {
            // TODO: Add your rollback modifications here
        });
    }
};
";
    }

    private function getBlankMigrationStub(): string
    {
        return "<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TODO: Implement migration logic
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // TODO: Implement rollback logic
    }
};
";
    }
}
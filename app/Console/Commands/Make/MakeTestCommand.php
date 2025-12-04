<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeTestCommand extends Command
{
    protected $signature = 'make:test 
                            {module : The module name} 
                            {feature : The feature name}
                            {name : The test name}
                            {type : The test type (feature or unit)}
                            {--service : Generate test for service}
                            {--force}';

    protected $description = 'Create a new test file';

    public function handle(): int
    {
        $module = $this->argument('module');
        $feature = $this->argument('feature');
        $name = $this->argument('name');
        $type = $this->argument('type');
        $is_service = $this->option('service');
        $force = $this->option('force');

        // Validate inputs
        if (!$this->validateInputs($module, $feature, $name, $type))
            return self::FAILURE;

        $module_name = Str::studly($module);
        $feature_name = Str::studly($feature);
        $test_name = Str::studly($name);
        $test_type = strtolower($type);

        // Ensure test name ends with 'Test'
        if (!Str::endsWith($test_name, 'Test')) {
            $test_name .= 'Test';
        }

        try {
            $this->createTest($module_name, $feature_name, $test_name, $test_type, $is_service, $force);

            $this->info("Test created successfully!");
            $this->info("File: modules/{$module_name}/Tests/" . Str::studly($test_type) . "/" . ($is_service ? "Services/{$feature_name}/" : "{$feature_name}/") . "{$test_name}.php");
        } catch (\Exception $e) {
            $this->error("Failed to create test: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $feature, string $name, string $type): bool
    {
        if (empty($module) || empty($feature) || empty($name) || empty($type)) {
            $this->error('Module, feature, name, and type are required.');
            return false;
        }

        $module_path = base_path("modules/" . Str::studly($module));
        if (!is_dir($module_path)) {
            $this->error("Module '{$module}' does not exist. Please create the module first.");
            return false;
        }

        if (!in_array(strtolower($type), ['feature', 'unit'])) {
            $this->error("Type must be either 'feature' or 'unit'.");
            return false;
        }

        return true;
    }

    private function createTest(string $module, string $feature, string $test_name, string $type, bool $is_service, bool $force): void
    {
        $test_path = $this->getTestPath($module, $feature, $test_name, $type, $is_service);
        
        if (file_exists($test_path) && !$force) {
            $this->error("Test {$test_name} already exists. Use --force to overwrite.");
            throw new \Exception("Test already exists");
        }

        // Create directory if it doesn't exist
        $test_dir = dirname($test_path);
        if (!is_dir($test_dir))
            mkdir($test_dir, 0755, true);

        $test_stub = $this->getTestStub($module, $feature, $test_name, $type, $is_service);
        file_put_contents($test_path, $test_stub);
    }

    private function getTestPath(string $module, string $feature, string $test_name, string $type, bool $is_service): string
    {
        $base_path = base_path("modules/{$module}/Tests");
        
        if ($is_service) {
            // Structure: modules/{Module}/Tests/{Feature|Unit}/Services/{Feature}/{TestName}.php
            return "{$base_path}/" . Str::studly($type) . "/Services/{$feature}/{$test_name}.php";
        } else {
            // Structure: modules/{Module}/Tests/{Feature|Unit}/{Feature}/{TestName}.php
            return "{$base_path}/" . Str::studly($type) . "/{$feature}/{$test_name}.php";
        }
    }

    private function getTestStub(string $module, string $feature, string $test_name, string $type, bool $is_service): string
    {
        if ($type === 'feature') {
            return $this->getFeatureTestStub($module, $feature, $test_name, $is_service);
        } else {
            return $this->getUnitTestStub($module, $feature, $test_name, $is_service);
        }
    }

    private function getFeatureTestStub(string $module, string $feature, string $test_name, bool $is_service): string
    {
        $namespace_path = $is_service ? "Services\\{$feature}" : $feature;
        
        return "<?php

namespace Modules\\{$module}\\Tests\\Feature\\{$namespace_path};

use Tests\\TestCase;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;

class {$test_name} extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
    }

    // TODO: Implement feature tests
}
";
    }

    private function getUnitTestStub(string $module, string $feature, string $test_name, bool $is_service): string
    {
        $namespace_path = $is_service ? "Services\\{$feature}" : $feature;

        $stub = "<?php

namespace Modules\\{$module}\\Tests\\Unit\\{$namespace_path};";

        if ($is_service) {
            $stub .= "

use Tests\\TestCase;
use Illuminate\\Foundation\\Testing\\RefreshDatabase;

class {$test_name} extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
    }

    // TODO: Implement unit tests
}
";
        } else {
            $stub .= "

use PHPUnit\Framework\TestCase;

class {$test_name} extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
    }

    // TODO: Implement unit tests
}
";
        }

        return $stub;
    }
}
<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class MakeRequestCommand extends Command
{
    protected $signature = 'make:request 
                           {module : The module name}
                           {feature : The feature name}
                           {request : The request name}
                           {client : The client name (Web/Admin/Mobile)}';

    protected $description = 'Create a new form request in a specific module';

    public function handle(): int
    {
        $module_name = Str::studly($this->argument('module'));
        $feature_name = Str::studly($this->argument('feature'));
        $request_name = Str::studly($this->argument('request'));
        $client = Str::studly($this->argument('client'));

        if (!$this->validateInputs($module_name, $feature_name, $request_name, $client))
            return self::FAILURE;

        // Ensure request name ends with 'Request'
        if (Str::contains($request_name, 'request')) {
            $request_name = Str::replaceLast('request', 'Request', $request_name);
        } else if (!Str::endsWith($request_name, 'Request')) {
            $request_name .= 'Request';
        }

        // Ensure feature directory exists
        $module_path = base_path("modules/{$module_name}");
        $requests_path = "{$module_path}/Http/Requests/Api/{$client}/{$feature_name}";
        if (!is_dir($requests_path))
            mkdir($requests_path, 0755, true);

        $request_path = "{$requests_path}/{$request_name}.php";
        if (file_exists($request_path)) {
            $this->error("Request {$request_name} already exists in {$module_name} module!");
            return self::FAILURE;
        }

        try {
            // Generate request content
            $module_namespace = "Modules\\{$module_name}";
            $feature_namespace = "Api\\{$client}\\{$feature_name}";
            $content = $this->getRequestStub(
                $request_name,
                $module_namespace,
                $feature_namespace
            );

            // Create request file
            file_put_contents($request_path, $content);

            $this->info("Request {$request_name} created successfully in {$module_name} module!");
        } catch (\Exception $e) {
            $this->error("Failed to create request: " . $e->getMessage());
            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function validateInputs(string $module, string $feature, string $request, string $client): bool
    {
        if (empty($module) || empty($feature) || empty($request)) {
            $this->error('Module, feature, and request name are required.');
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

    private function getRequestStub(
        string $request_name,
        string $module_namespace,
        string $feature_namespace
    ): string {
        return "<?php

namespace {$module_namespace}\\Http\\Requests\\{$feature_namespace};

use Illuminate\\Foundation\\Http\\FormRequest;

class {$request_name} extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Prepare the data for validation.
     */
    public function prepareForValidation(): void
    {
        // \$this->merge([
        //     'key' => \$this->route('key'),
        // ]);
    }
}
";
    }
}
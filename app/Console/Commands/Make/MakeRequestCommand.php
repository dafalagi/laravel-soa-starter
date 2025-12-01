<?php

namespace App\Console\Commands\Make;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;

class MakeRequestCommand extends Command
{
    protected $signature = 'make:request 
                           {module : The module name}
                           {feature : The feature name}
                           {request : The request name}
                           {client : The client name (Web/Admin/Mobile)}';

    protected $description = 'Create a new form request in a specific module';

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
        $request_name = Str::studly($this->argument('request'));
        $client = Str::studly($this->argument('client'));

        // Validate module exists
        $module_path = base_path("modules/{$module_name}");
        if (!$this->filesystem->isDirectory($module_path)) {
            $this->error("Module {$module_name} does not exist!");
            return 1;
        }

        // Ensure request name ends with 'Request'
        if (Str::contains($request_name, 'request')) {
            $request_name = Str::replaceLast('request', 'Request', $request_name);
        } else if (!Str::endsWith($request_name, 'Request')) {
            $request_name .= 'Request';
        }

        // Ensure client argument is valid
        $valid_clients = ['Web', 'Admin', 'Mobile'];
        if (!in_array($client, $valid_clients)) {
            $this->error("Client must be one of: " . implode(', ', $valid_clients));
            return 1;
        }

        // Ensure feature directory exists
        $requests_path = "{$module_path}/Http/Requests/Api/{$client}/{$feature_name}";
        if (!$this->filesystem->isDirectory($requests_path)) {
            $this->filesystem->makeDirectory($requests_path, 0755, true);
        }

        $request_path = "{$module_path}/Http/Requests/Api/{$client}/{$feature_name}/{$request_name}.php";

        // Check if request already exists
        if ($this->filesystem->exists($request_path)) {
            $this->error("Request {$request_name} already exists in {$module_name} module!");
            return 1;
        }

        // Generate request content
        $module_namespace = "Modules\\{$module_name}";
        $feature_namespace = "Api\\{$client}\\{$feature_name}";
        $content = $this->getRequestStub(
            $request_name,
            $module_namespace,
            $feature_namespace,
            $module_name,
            $feature_name
        );

        // Create request file
        $this->filesystem->put($request_path, $content);

        $this->info("Request {$request_name} created successfully in {$module_name} module!");

        return 0;
    }

    private function getRequestStub(
        string $request_name,
        string $module_namespace,
        string $feature_namespace,
        string $module_name,
        string $feature_name
    ): string {
        $module_key = strtolower($module_name);
        $feature_key = Str::snake($feature_name);

        return "<?php

namespace {$module_namespace}\\Http\\Requests\\{$feature_namespace};

use Illuminate\\Foundation\\Http\\FormRequest;
use Illuminate\\Validation\\Rule;

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
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \\Illuminate\\Contracts\\Validation\\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // TODO: Add validation rules here
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('{$module_key}::{$feature_key}.validation.name_required'),
            'name.string' => __('{$module_key}::{$feature_key}.validation.name_string'),
            'name.max' => __('{$module_key}::{$feature_key}.validation.name_max'),
            'email.required' => __('{$module_key}::{$feature_key}.validation.email_required'),
            'email.email' => __('{$module_key}::{$feature_key}.validation.email_invalid'),
            'email.max' => __('{$module_key}::{$feature_key}.validation.email_max'),
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('{$module_key}::{$feature_key}.attributes.name'),
            'email' => __('{$module_key}::{$feature_key}.attributes.email'),
        ];
    }

    /**
     * Handle a failed validation attempt.
     *
     * @param  \\Illuminate\\Contracts\\Validation\\Validator  \$validator
     * @return void
     *
     * @throws \\Illuminate\\Validation\\ValidationException
     */
    protected function failedValidation(\\Illuminate\\Contracts\\Validation\\Validator \$validator)
    {
        throw new \\Illuminate\\Validation\\ValidationException(\$validator, response()->json([
            'message' => __('{$module_key}::{$feature_key}.validation.invalid_data'),
            'errors' => \$validator->errors(),
        ], 422));
    }
}
";
    }
}
# Make Service Command

The `make:service` command generates a new service class with its corresponding interface, following the Service-Oriented Architecture (SOA) pattern used throughout the application.

## Command Signature

```bash
php artisan make:service {module} {feature} {service} [--force]
```

## Parameters

- **module** (required): The module name where the service will be created
- **feature** (required): The feature name that groups related services
- **service** (required): The service name (will automatically append "Service" if not present)
- **--force** (optional): Overwrite existing files without confirmation

## Usage Examples

### Basic Service Creation

```bash
# Create a basic service
php artisan make:service Auth User CreateUser

# Create with force flag to overwrite existing files
php artisan make:service Auth User UpdateUser --force

# Create a service for different modules
php artisan make:service Product Category StoreCategory
php artisan make:service Order Payment ProcessPayment
```

### Generated Files Structure

When you run `php artisan make:service Auth User CreateUser`, the command generates:

```
modules/Auth/Services/User/
├── Contracts/
│   └── CreateUserServiceInterface.php
└── CreateUserService.php
```

## Generated Code Structure

### Service Interface

**File:** `modules/Auth/Services/User/Contracts/CreateUserServiceInterface.php`

```php
<?php

namespace Modules\Auth\Services\User\Contracts;

interface CreateUserServiceInterface
{
    /**
     * Execute the CreateUserService operation.
     * 
     * TODO: Define the appropriate DTO instead of mixed.
     */
    public function execute(mixed $dto, bool $sub_service = false): array;
}
```

### Service Class

**File:** `modules/Auth/Services/User/CreateUserService.php`

```php
<?php

namespace Modules\Auth\Services\User;

use App\Services\BaseService;
use Modules\Auth\Services\User\Contracts\CreateUserServiceInterface;

class CreateUserService extends BaseService implements CreateUserServiceInterface
{
    public function execute(mixed $dto, bool $sub_service = false): array
    {
        return parent::execute($dto->toArray(), $sub_service);
    }

    protected function process(mixed $dto): void
    {
        $dto = $this->prepare($dto);

        // TODO: Implement the service logic here

        $this->results['data'] = []; // Replace with actual data, e.g., UserResponseDTO::fromModel($model);
        $this->results['message'] = __(''); // Add appropriate success message;
    }

    private function prepare(array $dto): array
    {
        // TODO: Prepare data before processing (e.g., hash passwords, format data)
        
        return $dto;
    }

    protected function rules(array $dto): array
    {
        return [
            // TODO: Add validation rules
        ];
    }
}
```

## Integration with Existing Architecture

### 1. Service Layer Architecture

The generated services follow the established SOA patterns:

- **Extends BaseService**: Inherits transaction handling, validation, and error management
- **Implements Interface**: Ensures contract compliance and supports dependency injection
- **Structured Methods**: Follows the `execute()` → `process()` → `prepare()` → `rules()` pattern

### 2. Method Responsibilities

#### `execute(mixed $dto, bool $sub_service = false): array`
- Entry point for the service
- Handles DTO conversion and calls parent execution
- Returns standardized response array

#### `process(mixed $dto): void`
- Contains the core business logic
- Manipulates models and data
- Sets results in `$this->results` array

#### `prepare(array $dto): array`
- Preprocesses data before business logic
- Handles data formatting, encryption, etc.
- Returns prepared data array

#### `rules(array $dto): array`
- Defines validation rules for the service
- Returns Laravel validation rules array
- Validates input data before processing

### 3. Usage in Controllers

After creating the service, integrate it into your controllers:

```php
<?php

namespace Modules\Auth\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Modules\Auth\DTOs\User\Requests\CreateUserRequestDTO;
use Modules\Auth\Services\User\Contracts\CreateUserServiceInterface;

class UserController extends Controller
{
    public function __construct(
        private CreateUserServiceInterface $create_user_service
    ) {}

    public function store(CreateUserRequest $request)
    {
        $dto = CreateUserRequestDTO::fromRequest($request);
        $result = $this->create_user_service->execute($dto);
        
        return $this->response($result);
    }
}
```

### 4. Service Provider Registration

Register your service interfaces in the appropriate service provider:

```php
// In modules/Auth/Providers/AuthModuleServiceProvider.php
public function register(): void
{
    $this->app->bind(
        \Modules\Auth\Services\User\Contracts\CreateUserServiceInterface::class,
        \Modules\Auth\Services\User\CreateUserService::class
    );
}
```

## Customization Workflow

After generating the service, follow these steps to complete the implementation:

### 1. Define DTOs
Create the appropriate Request and Response DTOs:
```bash
php artisan make:dto Auth User CreateUser request
php artisan make:dto Auth User CreateUser response
```

### 2. Update Interface
Replace `mixed $dto` with the specific DTO type:
```php
use Modules\Auth\DTOs\User\Requests\CreateUserRequestDTO;

public function execute(CreateUserRequestDTO $dto, bool $sub_service = false): array;
```

### 3. Implement Business Logic
Fill in the `process()` method with your business logic:
```php
protected function process(mixed $dto): void
{
    $dto = $this->prepare($dto);

    $user = new User();
    $user->name = $dto['name'];
    $user->email = $dto['email'];
    $user->password = Hash::make($dto['password']);
    
    $this->prepareAuditStore($user);
    $user->save();

    $this->results['data'] = UserResponseDTO::fromModel($user);
    $this->results['message'] = __('auth::user.create.success');
}
```

### 4. Add Validation Rules
Define validation rules in the `rules()` method:
```php
protected function rules(array $dto): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users,email'],
        'password' => ['required', 'string', 'min:8', 'confirmed'],
    ];
}
```

### 5. Implement Data Preparation
Add any data preprocessing in the `prepare()` method:
```php
private function prepare(array $dto): array
{
    if (isset($dto['password'])) {
        $dto['password'] = Hash::make($dto['password']);
    }
    
    return $dto;
}
```

## Best Practices

### 1. Single Responsibility
Each service should handle one specific operation:
- ✅ `CreateUserService` - Creates a user
- ✅ `UpdateUserService` - Updates a user  
- ❌ `UserService` - Too broad, handles multiple operations

### 2. Naming Conventions
- Service names should be descriptive and action-oriented
- Use PascalCase for class names
- Interfaces should end with "Interface"
- Follow the pattern: `{Action}{Entity}Service`

### 3. Error Handling
Services automatically inherit error handling from `BaseService`:
- Validation errors are automatically thrown
- Database transactions are handled
- Consistent error response format

### 4. Testing
Create corresponding tests for your services:
```bash
# Create feature test
touch modules/Auth/Tests/Feature/Services/CreateUserServiceTest.php

# Create unit test  
touch modules/Auth/Tests/Unit/Services/CreateUserServiceTest.php
```

## Related Commands

- [`make:dto`](MAKE_DTO.md) - Create Data Transfer Objects for service input/output
- [`make:controller`](MAKE_CONTROLLER.md) - Create controllers that use services
- [`make:request`](MAKE_REQUEST.md) - Create form requests for validation
- [`make:resource`](MAKE_RESOURCE.md) - Create API resources for responses

## Validation & Error Handling

The command includes several validation checks:

- **Module Existence**: Verifies the target module exists
- **File Conflicts**: Prevents overwriting without `--force` flag
- **Directory Creation**: Automatically creates necessary directories
- **Input Validation**: Ensures all required parameters are provided

## Examples by Use Case

### Authentication Services
```bash
php artisan make:service Auth User LoginUser
php artisan make:service Auth User LogoutUser  
php artisan make:service Auth User RegisterUser
php artisan make:service Auth Password ResetPassword
```

### E-commerce Services
```bash
php artisan make:service Product Category CreateCategory
php artisan make:service Product Inventory UpdateStock
php artisan make:service Order Cart AddToCart
php artisan make:service Order Payment ProcessPayment
```

### Content Management Services
```bash
php artisan make:service Content Article PublishArticle
php artisan make:service Content Media UploadMedia
php artisan make:service User Profile UpdateProfile
```

This command streamlines the creation of service classes while maintaining consistency with the established SOA architecture patterns throughout your application.
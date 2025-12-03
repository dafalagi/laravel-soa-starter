# Make DTO Command

The `make:dto` command generates a new Data Transfer Object (DTO) class in a specific module, organizing DTOs by feature and type (request/response).

## Command Signature

```bash
php artisan make:dto {module} {feature} {name} {type}
```

## Arguments

| Argument | Description | Example |
|----------|-------------|---------|
| `module` | The module name where the DTO will be created | `Auth`, `Product`, `Order` |
| `feature` | The feature name for organizing DTOs | `User`, `Product`, `Authentication` |
| `name` | The DTO name | `StoreUser`, `UpdateUser`, `UserDetail` |
| `type` | The DTO type (request/response) | `request`, `response` |

## Generated Structure

DTOs are organized in a hierarchical structure within each module:

```
modules/
└── {Module}/
    └── DTOs/
        └── {Feature}/
            ├── Requests/
            │   └── {Name}RequestDTO.php
            └── Responses/
                └── {Name}ResponseDTO.php
```

## Usage Examples

### Request DTOs

```bash
# User management request DTOs
php artisan make:dto Auth User StoreUser request
php artisan make:dto Auth User UpdateUser request
php artisan make:dto Auth User DeleteUser request

# Authentication request DTOs
php artisan make:dto Auth Authentication Login request
php artisan make:dto Auth Authentication Register request
```

**Generated paths:**
- `modules/Auth/DTOs/User/Requests/StoreUserRequestDTO.php`
- `modules/Auth/DTOs/User/Requests/UpdateUserRequestDTO.php`
- `modules/Auth/DTOs/User/Requests/DeleteUserRequestDTO.php`
- `modules/Auth/DTOs/Authentication/Requests/LoginRequestDTO.php`
- `modules/Auth/DTOs/Authentication/Requests/RegisterRequestDTO.php`

### Response DTOs

```bash
# User management response DTOs
php artisan make:dto Auth User UserDetail response
php artisan make:dto Auth User UserList response

# Product response DTOs
php artisan make:dto Product Product ProductDetail response
php artisan make:dto Product Product ProductCard response
```

**Generated paths:**
- `modules/Auth/DTOs/User/Responses/UserDetailResponseDTO.php`
- `modules/Auth/DTOs/User/Responses/UserListResponseDTO.php`
- `modules/Product/DTOs/Product/Responses/ProductDetailResponseDTO.php`
- `modules/Product/DTOs/Product/Responses/ProductCardResponseDTO.php`

## Generated DTO Structure

### Request DTO Structure
```php
<?php

namespace Modules\{Module}\DTOs\{Feature}\Requests;

class {Name}RequestDTO
{
    public function __construct(
        // TODO: Add your properties here
    ) {}

    public static function fromArray(array $data): self
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
```

### Response DTO Structure
```php
<?php

namespace Modules\{Module}\DTOs\{Feature}\Responses;

use Illuminate\Support\Collection;
use Modules\{Module}\Models\{Feature};

class {Name}ResponseDTO
{
    public function __construct(
        // TODO: Add your properties here
    ) {}

    public static function fromModel({Feature} $model): self
    {
        return new self(
            // TODO: Map model data to properties
        );
    }

    public static function fromCollection(Collection $models): array
    {
        return array_map(fn({Feature} $model) => self::fromModel($model), $models->all());
    }

    public function toArray(): array
    {
        return [
            // TODO: Return array representation
        ];
    }
}
```

## Key Features

### Request DTOs
- **Constructor**: Readonly properties with named parameters
- **fromArray()**: Static factory method to create DTO from request data
- **toArray()**: Convert DTO back to array format
- **Immutable**: Using readonly properties for data integrity

### Response DTOs
- **Constructor**: Readonly properties for response data
- **fromModel()**: Static factory method to create DTO from Eloquent model
- **fromCollection()**: Batch convert collection of models to DTO array
- **toArray()**: Convert DTO to array for JSON serialization
- **Model Integration**: Automatically imports corresponding model class

## Customization Examples

### Request DTO Implementation
Based on your existing `StoreUserRequestDTO`:

```php
<?php

namespace Modules\Auth\DTOs\User\Requests;

class StoreUserRequestDTO
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?string $password_confirmation,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'] ?? null,
            password: $data['password'] ?? null,
            password_confirmation: $data['password_confirmation'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
        ];
    }
}
```

### Response DTO Implementation
Based on your existing `UserResponseDTO`:

```php
<?php

namespace Modules\Auth\DTOs\User\Responses;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Modules\Auth\Models\User;

class UserResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly string $email,
        public readonly ?Carbon $email_verified_at,
        public readonly int $version,
        public readonly Carbon $created_at,
        public readonly Carbon $updated_at,
        public readonly ?User $createdBy,
        public readonly ?User $updatedBy,
    ) {}

    public static function fromModel(User $user): self
    {
        return new self(
            id: $user->id,
            uuid: $user->uuid,
            email: $user->email,
            email_verified_at: $user->email_verified_at,
            version: $user->version,
            created_at: $user->created_at,
            updated_at: $user->updated_at,
            createdBy: $user->createdBy,
            updatedBy: $user->updatedBy,
        );
    }

    public static function fromCollection(Collection $users): array
    {
        return array_map(fn(User $user) => self::fromModel($user), $users->all());
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'version' => $this->version,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'createdBy' => $this->createdBy,
            'updatedBy' => $this->updatedBy,
        ];
    }
}
```

## Integration Examples

### Using Request DTOs in Controllers
```php
use Modules\Auth\DTOs\User\Requests\StoreUserRequestDTO;

class UserController extends ApiController
{
    public function store(StoreUserRequest $request): JsonResponse
    {
        // Convert validated request to DTO
        $dto = StoreUserRequestDTO::fromArray($request->validated());
        
        // Pass DTO to service
        $result = $this->userService->createUser($dto);
        
        return $this->response($result);
    }
}
```

### Using Response DTOs in Services
```php
use Modules\Auth\DTOs\User\Responses\UserResponseDTO;

class UserService
{
    public function getUserById(int $id): array
    {
        $user = User::findOrFail($id);
        $dto = UserResponseDTO::fromModel($user);
        
        return [
            'success' => true,
            'data' => $dto->toArray(),
        ];
    }
    
    public function getAllUsers(): array
    {
        $users = User::all();
        $dtos = UserResponseDTO::fromCollection($users);
        
        return [
            'success' => true,
            'data' => $dtos,
        ];
    }
}
```

## Naming Conventions

### DTO Names
- **Request DTOs**: Action-based names (`StoreUser`, `UpdateUser`, `LoginUser`)
- **Response DTOs**: Data-based names (`UserDetail`, `UserList`, `UserCard`)

### Automatic Suffixes
The command automatically appends the appropriate suffix:
- **Request**: `RequestDTO`
- **Response**: `ResponseDTO`

### Examples
```bash
# Input: StoreUser + request → Output: StoreUserRequestDTO
php artisan make:dto Auth User StoreUser request

# Input: UserDetail + response → Output: UserDetailResponseDTO  
php artisan make:dto Auth User UserDetail response
```

## Best Practices

### Request DTO Design
- Use nullable properties for optional fields
- Implement proper type hints (string, int, bool, etc.)
- Use readonly properties for immutability
- Handle array mapping carefully in `fromArray()`

### Response DTO Design
- Include all necessary fields for the response
- Use proper Carbon types for dates
- Handle relationships appropriately
- Consider performance for large collections

### Organization Strategy
- Group related DTOs by feature
- Use descriptive names that indicate the DTO purpose
- Keep request and response DTOs separate
- Follow consistent naming patterns

## Validation & Error Handling

The command performs several validations:

1. **Module Exists**: Verifies the specified module directory exists
2. **DTO Type**: Ensures type is either 'request' or 'response'
3. **Naming**: Automatically appends appropriate suffix (RequestDTO/ResponseDTO)
4. **Duplicate Check**: Prevents overwriting existing DTOs

## Directory Creation

The command automatically creates the directory structure if it doesn't exist:
- Creates feature directories (`{Module}/DTOs/{Feature}/`)
- Creates type directories (`Requests/` or `Responses/`)
- Maintains consistent folder structure across all modules

## Error Messages

The command provides clear error messages for common issues:

- **Module Not Found**: "Module {ModuleName} does not exist!"
- **Invalid Type**: "DTO type must be one of: request, response"
- **DTO Exists**: "DTO {DTOName} already exists in {ModuleName} module!"

## Success Output

Upon successful creation, the command outputs:
```
DTO {DTOName} created successfully in {ModuleName} module!
```

This organized approach ensures that DTOs are properly structured, maintain data integrity through immutable properties, and provide a consistent interface for data transfer between different layers of your application.
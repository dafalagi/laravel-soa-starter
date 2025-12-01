# Make Request Command

The `make:request` command generates a new form request class in a specific module with the same structured approach as controllers, organizing requests by client type and feature.

## Command Signature

```bash
php artisan make:request {module} {feature} {request} {client}
```

## Arguments

| Argument | Description | Example |
|----------|-------------|---------|
| `module` | The module name where the request will be created | `Auth`, `Product`, `Order` |
| `feature` | The feature name for organizing requests | `Authentication`, `User`, `Profile` |
| `request` | The request name | `Login`, `Register`, `UserStore`, `UserUpdate` |
| `client` | The client type (Web/Admin/Mobile) | `Web`, `Admin`, `Mobile` |

## Generated Structure

Form requests are organized in a hierarchical structure within each module, mirroring the controller structure:

```
modules/
└── {Module}/
    └── Http/
        └── Requests/
            └── Api/
                ├── Web/
                │   └── {Feature}/
                │       └── {Request}Request.php
                ├── Admin/
                │   └── {Feature}/
                │       └── {Request}Request.php
                └── Mobile/
                    └── {Feature}/
                        └── {Request}Request.php
```

## Usage Examples

### Authentication Requests

```bash
# Admin login request
php artisan make:request Auth Authentication Login Admin

# Web registration request  
php artisan make:request Auth Authentication Register Web

# Mobile password reset request
php artisan make:request Auth Authentication PasswordReset Mobile
```

**Generated paths:**
- `modules/Auth/Http/Requests/Api/Admin/Authentication/LoginRequest.php`
- `modules/Auth/Http/Requests/Api/Web/Authentication/RegisterRequest.php`
- `modules/Auth/Http/Requests/Api/Mobile/Authentication/PasswordResetRequest.php`

### User Management Requests

```bash
# Admin user management
php artisan make:request Auth User UserStore Admin
php artisan make:request Auth User UserUpdate Admin

# Web user profile requests
php artisan make:request Auth User ProfileUpdate Web

# Mobile user preferences
php artisan make:request Auth User PreferencesUpdate Mobile
```

**Generated paths:**
- `modules/Auth/Http/Requests/Api/Admin/User/UserStoreRequest.php`
- `modules/Auth/Http/Requests/Api/Admin/User/UserUpdateRequest.php`
- `modules/Auth/Http/Requests/Api/Web/User/ProfileUpdateRequest.php`
- `modules/Auth/Http/Requests/Api/Mobile/User/PreferencesUpdateRequest.php`

### Product Management Requests

```bash
# Admin product management
php artisan make:request Product Catalog ProductStore Admin
php artisan make:request Product Catalog ProductUpdate Admin

# Web product search
php artisan make:request Product Search ProductFilter Web

# Mobile product creation
php artisan make:request Product Catalog ProductCreate Mobile
```

## Generated Request Structure

Each generated form request includes:

### Basic Structure
```php
<?php

namespace Modules\{Module}\Http\Requests\Api\{Client}\{Feature};

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class {Request}Request extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => __('module::feature.validation.name_required'),
            // ... more custom messages
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => __('module::feature.attributes.name'),
            // ... more custom attributes
        ];
    }

    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        throw new \Illuminate\Validation\ValidationException($validator, response()->json([
            'message' => __('module::feature.validation.invalid_data'),
            'errors' => $validator->errors(),
        ], 422));
    }
}
```

### Key Features

- **Form Request Base**: Extends `FormRequest` for automatic validation
- **Authorization**: Ready-to-use authorize method (defaults to true)
- **Validation Rules**: Sample rules with common field types
- **Custom Messages**: Integrated with modular localization system
- **Custom Attributes**: Localized field attribute names
- **JSON Error Response**: Custom failed validation handling for API responses
- **Translation Integration**: Uses module-specific translation keys

## Generated Components

### 1. Authorization Method
```php
public function authorize(): bool
{
    return true; // TODO: Implement authorization logic
}
```

### 2. Validation Rules
```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'max:255'],
    ];
}
```

### 3. Custom Error Messages
```php
public function messages(): array
{
    return [
        'name.required' => __('auth::user.validation.name_required'),
        'email.required' => __('auth::user.validation.email_required'),
    ];
}
```

### 4. Field Attributes
```php
public function attributes(): array
{
    return [
        'name' => __('auth::user.attributes.name'),
        'email' => __('auth::user.attributes.email'),
    ];
}
```

### 5. JSON Error Response
```php
protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
{
    throw new \Illuminate\Validation\ValidationException($validator, response()->json([
        'message' => __('auth::user.validation.invalid_data'),
        'errors' => $validator->errors(),
    ], 422));
}
```

## Translation Integration

Generated requests automatically integrate with the modular localization system:

### Translation Keys Format
- **Messages**: `{module}::{feature}.validation.{field}_{rule}`
- **Attributes**: `{module}::{feature}.attributes.{field}`
- **General**: `{module}::{feature}.validation.invalid_data`

### Example Translation Keys
```php
// For Auth module, User feature
'auth::user.validation.name_required'
'auth::user.validation.email_invalid'
'auth::user.attributes.name'
'auth::user.attributes.email'
```

## Validation & Error Handling

The command performs several validations:

1. **Module Exists**: Verifies the specified module directory exists
2. **Request Naming**: Automatically appends "Request" if not present
3. **Client Validation**: Ensures client is one of: Web, Admin, Mobile
4. **Duplicate Check**: Prevents overwriting existing requests

## Directory Creation

The command automatically creates the directory structure if it doesn't exist:
- Creates feature directories (`{Module}/Http/Requests/Api/{Client}/{Feature}/`)
- Maintains consistent folder structure across all modules

## Integration with Controllers

Generated requests can be easily integrated with controllers:

```php
// In your controller
use Modules\Auth\Http\Requests\Api\Admin\User\UserStoreRequest;

class UserController extends ApiController
{
    public function store(UserStoreRequest $request): JsonResponse
    {
        // Request is automatically validated
        $validated_data = $request->validated();
        
        // Your business logic here
        return $this->response(['message' => 'User created successfully']);
    }
}
```

## Best Practices

### Naming Conventions
- **Module**: PascalCase (`Auth`, `ProductManagement`)
- **Feature**: PascalCase (`Authentication`, `UserProfile`) 
- **Request**: PascalCase describing the action (`UserStore`, `LoginRequest`)
- **Client**: PascalCase (`Web`, `Admin`, `Mobile`)

### Organization Strategy
- Group related requests by feature
- Use descriptive names that indicate the request purpose
- Separate client-specific validation logic
- Follow RESTful naming conventions (Store, Update, Index, etc.)

### Example Organization
```
Auth/Http/Requests/Api/
├── Admin/
│   ├── Authentication/
│   │   ├── LoginRequest.php
│   │   └── LogoutRequest.php
│   └── User/
│       ├── UserStoreRequest.php
│       ├── UserUpdateRequest.php
│       └── UserDeleteRequest.php
├── Web/
│   ├── Authentication/
│   │   ├── LoginRequest.php
│   │   ├── RegisterRequest.php
│   │   └── PasswordResetRequest.php
│   └── User/
│       └── ProfileUpdateRequest.php
└── Mobile/
    ├── Authentication/
    │   └── LoginRequest.php
    └── User/
        └── PreferencesUpdateRequest.php
```

## Error Messages

The command provides clear error messages for common issues:

- **Module Not Found**: "Module {ModuleName} does not exist!"
- **Invalid Client**: "Client must be one of: Web, Admin, Mobile"
- **Request Exists**: "Request {RequestName} already exists in {ModuleName} module!"

## Success Output

Upon successful creation, the command outputs:
```
Request {RequestName} created successfully in {ModuleName} module!
```

This organized approach ensures that form requests are properly structured, maintain consistency with the controller architecture, and provide robust validation with integrated localization support.
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

class {Request}Request extends FormRequest
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
        // $this->merge([
        //     'key' => $this->route('key'),
        // ]);
    }
}
```

### Key Features

- **Form Request Base**: Extends `FormRequest` for automatic validation
- **Authorization**: Ready-to-use authorize method (defaults to true)
- **Data Preparation**: `prepareForValidation()` method for pre-processing request data
- **Clean Structure**: Minimal boilerplate, ready for customization
- **Route Parameter Access**: Example of accessing route parameters in preparation method

## Generated Components

### 1. Authorization Method
```php
public function authorize(): bool
{
    return true; // TODO: Implement authorization logic
}
```

### 2. Data Preparation Method
```php
public function prepareForValidation(): void
{
    // $this->merge([
    //     'key' => $this->route('key'),
    // ]);
}
```

The generated request provides a clean foundation where you can add:
- **Validation Rules**: Add `rules()` method as needed
- **Custom Messages**: Add `messages()` method for localized error messages
- **Field Attributes**: Add `attributes()` method for field name translation
- **Data Transformation**: Use `prepareForValidation()` to modify request data before validation

## Customization

The generated request provides a minimal structure that you can extend as needed:

### Adding Validation Rules
```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'email', 'unique:users'],
        // Add your validation rules here
    ];
}
```

### Adding Custom Messages (Optional)
```php
public function messages(): array
{
    return [
        'name.required' => __('auth::user.validation.name_required'),
        'email.unique' => __('auth::user.validation.email_unique'),
        // Add custom messages with modular localization
    ];
}
```

### Data Preparation Examples
```php
public function prepareForValidation(): void
{
    $this->merge([
        'user_id' => $this->route('user'),
        'slug' => Str::slug($this->name),
        'formatted_phone' => $this->formatPhoneNumber($this->phone),
    ]);
}
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
use Modules\Auth\Http\Requests\Api\Admin\User\GetUserRequest;

class UserController extends ApiController
{
    public function index(GetUserRequest $request): JsonResponse
    {
        // Request is automatically authorized and prepared
        // Add validation rules to the request class as needed
        $data = $request->all();
        
        // Your business logic here
        return $this->response(['data' => $data]);
    }
}
```

### Adding Validation Rules Later
```php
// In your generated request class
public function rules(): array
{
    return [
        'page' => ['integer', 'min:1'],
        'per_page' => ['integer', 'min:1', 'max:100'],
        'search' => ['string', 'max:255'],
    ];
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
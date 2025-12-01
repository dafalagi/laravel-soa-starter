# Make Controller Command

The `make:controller` command generates a new controller in a specific module with a structured approach to organize API controllers by client type and feature.

## Command Signature

```bash
php artisan make:controller {module} {feature} {controller} {client}
```

## Arguments

| Argument | Description | Example |
|----------|-------------|---------|
| `module` | The module name where the controller will be created | `Auth`, `Product`, `Order` |
| `feature` | The feature name for organizing controllers | `Authentication`, `User`, `Profile` |
| `controller` | The controller name | `Login`, `Register`, `UserProfile` |
| `client` | The client type (Web/Admin/Mobile) | `Web`, `Admin`, `Mobile` |

## Generated Structure

Controllers are organized in a hierarchical structure within each module:

```
modules/
└── {Module}/
    └── Http/
        └── Controllers/
            └── Api/
                ├── Web/
                │   └── {Feature}/
                │       └── {Controller}Controller.php
                ├── Admin/
                │   └── {Feature}/
                │       └── {Controller}Controller.php
                └── Mobile/
                    └── {Feature}/
                        └── {Controller}Controller.php
```

## Usage Examples

### Authentication Controllers

```bash
# Admin login controller
php artisan make:controller Auth Authentication Login Admin

# Web registration controller  
php artisan make:controller Auth Authentication Register Web

# Mobile password reset controller
php artisan make:controller Auth Authentication PasswordReset Mobile
```

**Generated paths:**
- `modules/Auth/Http/Controllers/Api/Admin/Authentication/LoginController.php`
- `modules/Auth/Http/Controllers/Api/Web/Authentication/RegisterController.php`
- `modules/Auth/Http/Controllers/Api/Mobile/Authentication/PasswordResetController.php`

### User Management Controllers

```bash
# Admin user management
php artisan make:controller Auth User Profile Admin
php artisan make:controller Auth User Settings Admin

# Web user profile
php artisan make:controller Auth User Profile Web

# Mobile user preferences
php artisan make:controller Auth User Preferences Mobile
```

**Generated paths:**
- `modules/Auth/Http/Controllers/Api/Admin/User/ProfileController.php`
- `modules/Auth/Http/Controllers/Api/Admin/User/SettingsController.php`
- `modules/Auth/Http/Controllers/Api/Web/User/ProfileController.php`
- `modules/Auth/Http/Controllers/Api/Mobile/User/PreferencesController.php`

### Product Management Controllers

```bash
# Admin product management
php artisan make:controller Product Catalog Product Admin
php artisan make:controller Product Catalog Category Admin

# Web product browsing
php artisan make:controller Product Catalog Product Web
php artisan make:controller Product Search Product Web

# Mobile product views
php artisan make:controller Product Catalog Product Mobile
```

## Generated Controller Structure

Each generated controller includes:

### Basic Structure
```php
<?php

namespace Modules\{Module}\Http\Controllers\Api\{Client}\{Feature};

use App\Http\Controllers\ApiController;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class {Controller}Controller extends ApiController
{
    public function __construct()
    {
    }

    public function index(Request $request): JsonResponse
    {
        return $this->response([]);
    }

    public function show(Request $request): JsonResponse
    {
        return $this->response([]);
    }

    public function store(Request $request): JsonResponse
    {
        return $this->response([]);
    }

    public function update(Request $request): JsonResponse
    {
        return $this->response([]);
    }

    public function destroy(Request $request): JsonResponse
    {
        return $this->response([]);
    }
}
```

### Key Features

- **Extends ApiController**: Inherits common API functionality
- **Standard CRUD Methods**: Includes index, show, store, update, destroy methods
- **JsonResponse Type**: All methods return proper JSON responses
- **Constructor Ready**: Empty constructor for dependency injection
- **Response Helper**: Uses `$this->response()` helper method

## Validation & Error Handling

The command performs several validations:

1. **Module Exists**: Verifies the specified module directory exists
2. **Controller Naming**: Automatically appends "Controller" if not present
3. **Client Validation**: Ensures client is one of: Web, Admin, Mobile
4. **Duplicate Check**: Prevents overwriting existing controllers

## Directory Creation

The command automatically creates the directory structure if it doesn't exist:
- Creates feature directories (`{Module}/Http/Controllers/Api/{Client}/{Feature}/`)
- Maintains consistent folder structure across all modules

## Integration with Module Architecture

### Namespace Convention
Controllers follow the namespace pattern:
```
Modules\{Module}\Http\Controllers\Api\{Client}\{Feature}\{Controller}Controller
```

### Route Organization
Generated controllers can be organized in route files by client and feature:

```php
// routes/api/{client}/{feature}.php
Route::prefix('api/v1/{client}/{feature}')->group(function () {
    Route::apiResource('resource', ControllerName::class);
});
```

## Best Practices

### Naming Conventions
- **Module**: PascalCase (`Auth`, `ProductManagement`)
- **Feature**: PascalCase (`Authentication`, `UserProfile`) 
- **Controller**: PascalCase without "Controller" suffix (`Login`, `Register`)
- **Client**: PascalCase (`Web`, `Admin`, `Mobile`)

### Organization Strategy
- Group related controllers by feature
- Separate client-specific logic into different controllers
- Use descriptive feature names that represent business capabilities

### Example Organization
```
Auth/Http/Controllers/Api/
├── Admin/
│   ├── Authentication/
│   │   ├── LoginController.php
│   │   └── LogoutController.php
│   └── User/
│       ├── ProfileController.php
│       └── PermissionController.php
├── Web/
│   ├── Authentication/
│   │   ├── LoginController.php
│   │   ├── RegisterController.php
│   │   └── PasswordResetController.php
│   └── User/
│       └── ProfileController.php
└── Mobile/
    ├── Authentication/
    │   └── LoginController.php
    └── User/
        └── PreferencesController.php
```

## Error Messages

The command provides clear error messages for common issues:

- **Module Not Found**: "Module {ModuleName} does not exist!"
- **Invalid Client**: "Client must be one of: Web, Admin, Mobile"
- **Controller Exists**: "Controller {ControllerName} already exists in {ModuleName} module!"

## Success Output

Upon successful creation, the command outputs:
```
Controller {ControllerName} created successfully in {ModuleName} module!
```

This organized approach ensures that controllers are properly structured, easy to locate, and maintainable as the application grows across different client types and features.
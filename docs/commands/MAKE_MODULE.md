# Make Module Command

The `make:module` command generates a new module with a complete directory structure, service provider, and route file. This command streamlines the creation of modular components in the Laravel SOA Starter architecture.

## Command Signature

```bash
php artisan make:module {name}
```

## Arguments

| Argument | Description | Example |
|----------|-------------|---------|
| `name` | The name of the module to create | `Auth`, `Product`, `Order`, `Payment` |

## Generated Structure

The command creates a fully structured module directory with all necessary subdirectories:

```
modules/
└── {Module}/
    ├── DTOs/
    ├── Http/
    │   └── Controllers/
    │       └── Api/
    ├── Models/
    ├── Providers/
    │   └── {Module}ModuleServiceProvider.php
    ├── Routes/
    │   └── api.php
    ├── Services/
    ├── Tests/
    │   ├── Feature/
    │   └── Unit/
    ├── Database/
    │   ├── Migrations/
    │   ├── Factories/
    │   └── Seeders/
    └── Resources/
        └── lang/
            └── en/
```

## Usage Examples

### Creating Authentication Module

```bash
php artisan make:module Auth
```

**Output:**
```
Creating module: Auth
Module Auth created successfully!
Don't forget to register the service provider in bootstrap/providers.php:
Modules\Auth\Providers\AuthModuleServiceProvider::class,
```

### Creating Product Module

```bash
php artisan make:module Product
```

### Creating Order Module

```bash
php artisan make:module Order
```

## Generated Files

### 1. Module Service Provider

**File:** `Providers/{Module}ModuleServiceProvider.php`

The service provider handles:
- Service registration and binding
- Route loading from the module's `Routes/api.php`
- Database migration loading
- Translation file loading
- Module-specific console commands

**Example:**
```php
<?php

namespace Modules\Auth\Providers;

use Illuminate\Support\ServiceProvider;

class AuthModuleServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind services to the container
        // $this->app->bind(SomeServiceInterface::class, SomeService::class);
    }

    public function boot(): void
    {
        $this->loadRoutes();
        $this->loadMigrations();
        $this->loadTranslations();

        if ($this->app->runningInConsole()) {
            $this->registerCommands();
        }
    }

    private function loadRoutes(): void
    {
        if (file_exists(__DIR__ . '/../Routes/api.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../Routes/api.php');
        }
    }

    private function loadMigrations(): void
    {
        if (is_dir(__DIR__ . '/../Database/Migrations')) {
            $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        }
    }

    private function loadTranslations(): void
    {
        $translationPath = __DIR__ . '/../Resources/lang';
        if (is_dir($translationPath)) {
            $this->loadTranslationsFrom($translationPath, 'auth');
        }
    }

    private function registerCommands(): void
    {
        // Register module-specific commands here
    }
}
```

### 2. API Routes File

**File:** `Routes/api.php`

Provides a template for defining module routes with API prefix:

```php
<?php

use Illuminate\Support\Facades\Route;

/*|--------------------------------------------------------------------------
| Auth Module API Routes
|--------------------------------------------------------------------------
| Here is where you can register API routes for the Auth module. These
| routes are loaded by the AuthModuleServiceProvider within a group which
| is assigned the "api" middleware group.
|--------------------------------------------------------------------------*/

Route::prefix('api/v0')->middleware(['api'])->group(function () {
    // require __DIR__ . '/some-directory/some-routes-file.php';
});
```

## Directory Breakdown

### DTOs (Data Transfer Objects)
Store request/response data transfer objects for your module:
```
DTOs/
├── CreateUserDTO.php
├── UpdateUserDTO.php
└── UserResponseDTO.php
```

### Http/Controllers/Api
Contains API controllers organized by client type:
```
Http/Controllers/Api/
├── Web/
├── Admin/
└── Mobile/
```

### Models
Eloquent models specific to this module:
```
Models/
├── User.php
├── Role.php
└── Permission.php
```

### Services
Business logic services extending `BaseService`:
```
Services/
├── CreateUserService.php
├── UpdateUserService.php
├── DeleteUserService.php
└── AuthenticationService.php
```

### Tests
Unit and feature tests for the module:
```
Tests/
├── Unit/
│   ├── Services/
│   └── Models/
└── Feature/
    ├── Controllers/
    └── API/
```

### Database
Database-related files:
```
Database/
├── Migrations/
│   └── 2024_01_01_000000_create_users_table.php
├── Factories/
│   └── UserFactory.php
└── Seeders/
    └── UserSeeder.php
```

### Resources/lang
Translation files for the module:
```
Resources/lang/en/
├── messages.php
├── validation.php
└── errors.php
```

## Registration

After creating a module, you must register its service provider in `bootstrap/providers.php`:

```php
<?php

return [
    App\Providers\AppServiceProvider::class,
    Modules\Auth\Providers\AuthModuleServiceProvider::class,
    Modules\Product\Providers\ProductModuleServiceProvider::class,
    // Add more module providers here
];
```

The command output displays the exact line to add.

## Validation & Error Handling

The command performs several validations:

1. **Module Name**: Validates that the module name is not empty
2. **Duplicate Check**: Prevents creating a module that already exists
3. **Directory Creation**: Automatically creates all necessary directories

**Error Messages:**
- "Module name cannot be empty." - Name validation failed
- "Module {name} already exists!" - Module directory already exists
- "Failed to create module: {error}" - General creation error

## Best Practices

### Naming Conventions
- Use **PascalCase** for module names: `Auth`, `Product`, `PaymentGateway`
- Use **singular nouns** when possible: `User` instead of `Users`
- Use **business domain names**: `Order` rather than `OrderManagement`

### Module Organization
1. Create a module for each business domain/feature
2. Keep modules as self-contained units
3. Use services for business logic (not controllers)
4. Use DTOs for request/response validation
5. Write tests within each module

### Post-Creation Steps

After running `make:module`, follow these steps:

1. **Register the service provider** in `bootstrap/providers.php`
2. **Create controllers** using `make:controller` command
3. **Create services** for business logic
4. **Define routes** in `Routes/api.php`
5. **Create migrations** in `Database/Migrations/`
6. **Write tests** in `Tests/` directories

### Example Workflow

```bash
# 1. Create the module
php artisan make:module Product

# 2. Create controllers for different clients
php artisan make:controller Product Product Admin
php artisan make:controller Product Product Web
php artisan make:controller Product Category Admin

# 3. Create a model
php artisan make:model Modules/Product/Models/Product

# 4. Create a service
php artisan make:service Modules/Product/Services/CreateProductService

# 5. Create a migration
php artisan make:migration create_products_table --path=modules/Product/Database/Migrations

# 6. Register the service provider in bootstrap/providers.php
# Modules\Product\Providers\ProductModuleServiceProvider::class,

# 7. Define routes in modules/Product/Routes/api.php
# 8. Write tests in modules/Product/Tests/
```

## Integration with Module Architecture

### Service Provider Auto-Loading
The service provider automatically:
- Loads routes from `Routes/api.php`
- Registers migrations from `Database/Migrations/`
- Loads translation files from `Resources/lang/`
- Registers console commands if running in console

### Route Loading
Routes are loaded with the API prefix and middleware:
```php
Route::prefix('api/v0')->middleware(['api'])->group(function () {
    // Your routes here
});
```

### Database Integration
Migrations are automatically discovered and loaded when running:
```bash
php artisan migrate
```

### Translation Loading
Translation files are loaded with the module name as namespace:
```php
// Access translations with module namespace
trans('auth.messages.welcome')
```

## Related Commands

- **[make:controller](MAKE_CONTROLLER.md)** - Create controllers within a module
- **make:model** - Create Eloquent models
- **make:migration** - Create database migrations
- **make:service** - Create service classes

## Troubleshooting

### Module Not Found After Creation
**Issue:** Module appears in filesystem but not loaded by Laravel

**Solution:** Ensure the service provider is registered in `bootstrap/providers.php`:
```php
Modules\YourModule\Providers\YourModuleServiceProvider::class,
```

### Routes Not Loading
**Issue:** Module routes return 404

**Solution:** Verify `Routes/api.php` exists and the service provider's `loadRoutes()` method can find it

### Migrations Not Running
**Issue:** Module migrations not executing with `php artisan migrate`

**Solution:** Ensure migrations are in `Database/Migrations/` directory and follow Laravel naming conventions

## Success Output

Upon successful creation:
```
Creating module: YourModule
Module YourModule created successfully!
Don't forget to register the service provider in bootstrap/providers.php:
Modules\YourModule\Providers\YourModuleServiceProvider::class,
```

The module is now ready to use. Add its service provider to `bootstrap/providers.php` to activate it.

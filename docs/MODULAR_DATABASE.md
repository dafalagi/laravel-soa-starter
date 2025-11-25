# Modular Database Architecture

This Laravel SOA starter follows a modular monolith pattern where each module contains its own database-related files. This provides better separation of concerns and makes modules more portable.

## Module Database Structure

Each module has its own `Database/` directory containing:

```
modules/ModuleName/Database/
├── Migrations/         # Module-specific migrations
├── Factories/         # Model factories for testing
└── Seeders/          # Module-specific seeders
```

### Example: Auth Module

```
modules/Auth/Database/
├── Migrations/
│   └── 2024_01_01_000001_create_users_table.php
├── Factories/
│   └── UserFactory.php
└── Seeders/
    └── AuthSeeder.php
```

## Benefits

### ✅ **Separation of Concerns**
- Each module manages its own database schema
- Changes to one module don't affect others
- Clear ownership of database tables

### ✅ **Portability**
- Modules can be easily moved between projects
- Self-contained with all database dependencies
- Independent deployment capabilities

### ✅ **Testing**
- Module-specific factories for test data
- Isolated test environments per module
- Better test organization

### ✅ **Team Development**
- Teams can work on modules independently
- Reduced merge conflicts in database files
- Clear boundaries for code reviews

## Usage

### Creating a New Module
```bash
php artisan make:module ModuleName
```

This creates a complete module structure including:
- Migration file with basic table structure
- Model with proper factory reference
- Factory for test data generation
- Seeder for database seeding

### Module Service Provider
Each module's service provider automatically:
- Registers the module's migrations
- Loads module routes
- Binds service contracts

```php
// In ModuleServiceProvider
public function boot(): void
{
    $this->loadMigrations();  // Loads module migrations
    $this->loadRoutes();      // Loads module routes
}
```

### Running Migrations
Module migrations are automatically discovered:
```bash
php artisan migrate  # Runs all migrations including module migrations
```

### Seeding Data
Add module seeders to `DatabaseSeeder`:
```php
public function run(): void
{
    $this->call([
        AuthSeeder::class,
        YourModuleSeeder::class,
    ]);
}
```

### Model Factories
Module models automatically discover their factories using the `HasModularFactory` trait:
```php
// In Auth/Models/User.php
use App\Traits\HasModularFactory;

class User extends Model
{
    use HasModularFactory;
    
    // Factory automatically discovered as:
    // \Modules\Auth\Database\Factories\UserFactory
}
```

### Testing
Tests automatically use module factories:
```php
// Creates user using Auth module's factory
$user = User::factory()->create();
```

## Migration Naming Convention

Module migrations follow this pattern:
- **File**: `{timestamp}_create_{table_name}_table.php`
- **Class**: `CreateUsersTable` (follows Laravel conventions)
- **Table**: Uses module's domain (e.g., `users`, `products`, `orders`)

## Factory Conventions

Module factories are namespaced and self-contained:
```php
namespace Modules\Auth\Database\Factories;

use Modules\Auth\Models\User;

class UserFactory extends Factory
{
    protected $model = User::class;
    
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            // ...
        ];
    }
}
```

## Best Practices

### ✅ **DO**
- Keep module migrations in the module's Database/Migrations directory
- Use module-specific factories and seeders
- Name tables according to the module's domain
- Include all database-related files in the module

### ❌ **DON'T**
- Mix module migrations with application migrations
- Create cross-module database dependencies in migrations
- Reference other modules' factories directly
- Put shared database files in global directories

## Migration Dependencies

When modules need to reference other modules' tables:

### Option 1: Use Foreign Keys with Strings
```php
// Instead of foreign key constraints
$table->unsignedBigInteger('user_id');
$table->index('user_id');
```

### Option 2: Module Events/Services
```php
// Use events or services for cross-module operations
event(new UserCreated($user));
```

### Option 3: Shared Tables Module
Create a separate module for shared database concerns if needed.

## Testing with Module Databases

Tests automatically use module-specific factories:

```php
// In Auth module tests
$user = User::factory()->create();          // Uses Auth/Database/Factories/UserFactory
$admin = User::factory()->admin()->create(); // Uses custom factory states
```

This modular approach ensures that each module is self-contained while maintaining the benefits of a monolithic deployment.
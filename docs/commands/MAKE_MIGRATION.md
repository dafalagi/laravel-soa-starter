# Make Migration Command

The `make:migration` command generates new migration files in the specified module, following the modular architecture pattern used throughout the application.

## Command Signature

```bash
php artisan make:migration {module} {name} [--create=] [--table=] [--force]
```

## Parameters

- **module** (required): The module name where the migration will be created
- **name** (required): The migration name (will be converted to snake_case)
- **--create** (optional): The table name to be created (generates create table migration)
- **--table** (optional): The table name to modify (generates modify table migration)
- **--force** (optional): Overwrite existing files without confirmation

## Usage Examples

### Create Table Migration

```bash
# Create a new table migration
php artisan make:migration Auth create_profiles_table --create=auth_profiles

# Create a new table with force flag
php artisan make:migration Auth create_users_table --create=auth_users --force
```

### Modify Table Migration

```bash
# Create a table modification migration
php artisan make:migration Auth add_avatar_to_users_table --table=auth_users

# Add index to existing table
php artisan make:migration Auth add_email_index_to_profiles --table=auth_profiles
```

### Blank Migration

```bash
# Create a blank migration for custom operations
php artisan make:migration Auth seed_initial_data

# Create migration for data transformations
php artisan make:migration Auth migrate_legacy_user_data
```

### Generated Files Structure

When you run `php artisan make:migration Auth create_profiles_table --create=auth_profiles`, the command generates:

```
modules/Auth/Database/Migrations/
└── 2025_12_22_143052_000001_create_profiles_table.php
```

## Generated Code Structure

### Create Table Migration

**Command:** `php artisan make:migration Auth create_profiles_table --create=auth_profiles`

**File:** `modules/Auth/Database/Migrations/2025_12_22_143052_000001_create_profiles_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auth_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid()->unique();

            // TODO: Add your table columns here
            
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(0);
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();
            $table->integer('created_at')->nullable();
            $table->integer('updated_at')->nullable();
            $table->integer('deleted_at')->nullable();

            $table->index([
                'id',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auth_profiles');
    }
};
```

### Modify Table Migration

**Command:** `php artisan make:migration Auth add_avatar_to_users --table=auth_users`

**File:** `modules/Auth/Database/Migrations/2025_12_22_143052_000002_add_avatar_to_users.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('auth_users', function (Blueprint $table) {
            // TODO: Add your table modifications here
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('auth_users', function (Blueprint $table) {
            // TODO: Add your rollback modifications here
        });
    }
};
```

### Blank Migration

**Command:** `php artisan make:migration Auth seed_initial_data`

**File:** `modules/Auth/Database/Migrations/2025_12_22_143052_000003_seed_initial_data.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // TODO: Implement migration logic
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // TODO: Implement rollback logic
    }
};
```

## Integration with Existing Architecture

### 1. Modular File Organization

Migrations are created in module-specific directories:
- Path: `modules/{Module}/Database/Migrations/`
- Follows Laravel's standard migration structure
- Integrates with `php artisan migrate` command
- Supports module-specific migration paths

### 2. Timestamp and Sequence Management

The command uses intelligent timestamping:
- Format: `YYYY_MM_DD_HHMMSS_sequence_name.php`
- Automatic sequence numbering prevents conflicts
- Sequence resets daily and increments across all modules
- Examples:
  - `2025_12_22_143052_000001_create_profiles_table.php`
  - `2025_12_22_143052_000002_add_avatar_to_users.php`

### 3. Standard Audit Fields

Create table migrations include standard audit fields:
```php
$table->id();                              // Internal ID
$table->uuid()->unique();                  // Public UUID
$table->boolean('is_active')->default(true); // Active status
$table->integer('version')->default(0);     // Optimistic locking
$table->integer('created_by')->nullable();  // Audit: created by
$table->integer('updated_by')->nullable();  // Audit: updated by
$table->integer('deleted_by')->nullable();  // Audit: deleted by
$table->integer('created_at')->nullable();  // Unix timestamp
$table->integer('updated_at')->nullable();  // Unix timestamp
$table->integer('deleted_at')->nullable();  // Unix timestamp (soft delete)
```

### 4. Consistent Indexing

Standard index on primary fields:
```php
$table->index([
    'id',
]);
```

## Customization Workflow

After generating the migration, follow these steps to complete the implementation:

### 1. Add Table Columns (Create Migration)

Define your specific table structure:
```php
public function up(): void
{
    Schema::create('auth_profiles', function (Blueprint $table) {
        $table->id();
        $table->uuid()->unique();

        // Custom columns
        $table->string('name');
        $table->string('email')->unique();
        $table->text('bio')->nullable();
        $table->string('avatar_url')->nullable();
        $table->date('birth_date')->nullable();
        $table->json('preferences')->nullable();
        
        // Foreign keys
        $table->uuid('user_uuid');
        $table->foreign('user_uuid')->references('uuid')->on('auth_users');
        
        // Standard audit fields
        $table->boolean('is_active')->default(true);
        $table->integer('version')->default(0);
        $table->integer('created_by')->nullable();
        $table->integer('updated_by')->nullable();
        $table->integer('deleted_by')->nullable();
        $table->integer('created_at')->nullable();
        $table->integer('updated_at')->nullable();
        $table->integer('deleted_at')->nullable();

        // Indexes
        $table->index(['uuid', 'is_active']);
        $table->index('email');
        $table->index('user_uuid');
    });
}
```

### 2. Add Table Modifications (Modify Migration)

Define specific modifications:
```php
public function up(): void
{
    Schema::table('auth_users', function (Blueprint $table) {
        // Add columns
        $table->string('avatar_url')->nullable()->after('email');
        $table->timestamp('last_login_at')->nullable();
        
        // Add indexes
        $table->index('last_login_at');
        
        // Modify existing columns
        $table->string('email', 191)->change(); // Reduce length
    });
}

public function down(): void
{
    Schema::table('auth_users', function (Blueprint $table) {
        // Remove columns
        $table->dropColumn(['avatar_url', 'last_login_at']);
        
        // Remove indexes
        $table->dropIndex(['last_login_at']);
        
        // Revert column changes
        $table->string('email', 255)->change();
    });
}
```

### 3. Implement Custom Logic (Blank Migration)

Add custom migration logic:
```php
public function up(): void
{
    // Data seeding
    DB::table('auth_roles')->insert([
        ['name' => 'admin', 'created_at' => time()],
        ['name' => 'user', 'created_at' => time()],
    ]);
    
    // Data transformation
    $users = DB::table('auth_users')->whereNull('uuid')->get();
    foreach ($users as $user) {
        DB::table('auth_users')
            ->where('id', $user->id)
            ->update(['uuid' => Str::uuid()]);
    }
    
    // Schema operations
    Schema::table('auth_users', function (Blueprint $table) {
        $table->uuid('uuid')->unique()->change();
    });
}

public function down(): void
{
    // Reverse operations
    DB::table('auth_roles')->truncate();
    
    Schema::table('auth_users', function (Blueprint $table) {
        $table->uuid('uuid')->nullable()->change();
    });
}
```

## Best Practices

### 1. Naming Conventions
- Use descriptive migration names
- Follow the pattern: `{action}_{entity}_table` or `{action}_{description}`
- Use snake_case for migration names
- Examples:
  - `create_profiles_table`
  - `add_avatar_to_users`
  - `update_user_email_constraints`

### 2. Foreign Key Management
- Use UUIDs for foreign key relationships
- Always define foreign key constraints
- Use consistent naming: `{entity}_uuid`
- Include proper indexes on foreign keys

### 3. Index Strategy
- Add indexes on frequently queried columns
- Include composite indexes for complex queries
- Consider unique indexes for business rules
- Don't over-index (impacts write performance)

### 4. Rollback Safety
- Always implement proper `down()` methods
- Test rollback scenarios
- Consider data loss implications
- Use transactions for complex operations

### 5. Data Types
- Use appropriate column types for data
- Consider storage requirements
- Use nullable for optional fields
- Define proper string lengths

## Migration Execution

### Run Migrations

```bash
# Run all pending migrations
php artisan migrate

# Run migrations for specific module
php artisan migrate --path=modules/Auth/Database/Migrations

# Run migrations with output
php artisan migrate --verbose
```

### Rollback Migrations

```bash
# Rollback last batch
php artisan migrate:rollback

# Rollback specific number of batches
php artisan migrate:rollback --step=3

# Rollback all migrations
php artisan migrate:reset
```

### Migration Status

```bash
# Check migration status
php artisan migrate:status

# Show pending migrations
php artisan migrate:status --pending
```

## Testing Integration

Create migration tests to ensure schema integrity:

```bash
# Create migration test
touch modules/Auth/Tests/Feature/Migrations/CreateProfilesTableTest.php
```

**Example Test:**

```php
<?php

namespace Modules\Auth\Tests\Feature\Migrations;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class CreateProfilesTableTest extends TestCase
{
    use RefreshDatabase;

    public function test_profiles_table_has_expected_columns(): void
    {
        $columns = [
            'id', 'uuid', 'name', 'email', 'bio', 'avatar_url',
            'user_uuid', 'is_active', 'version', 'created_by',
            'updated_by', 'deleted_by', 'created_at', 'updated_at', 'deleted_at'
        ];

        foreach ($columns as $column) {
            $this->assertTrue(
                Schema::hasColumn('auth_profiles', $column),
                "Table auth_profiles should have column {$column}"
            );
        }
    }

    public function test_profiles_table_has_proper_indexes(): void
    {
        $indexes = [
            'auth_profiles_uuid_unique',
            'auth_profiles_email_index',
            'auth_profiles_user_uuid_index',
        ];

        foreach ($indexes as $index) {
            $this->assertTrue(
                $this->hasIndex('auth_profiles', $index),
                "Table auth_profiles should have index {$index}"
            );
        }
    }

    private function hasIndex(string $table, string $index): bool
    {
        $indexes = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableIndexes($table);

        return isset($indexes[$index]);
    }
}
```

## Related Commands

- [`make:model`](MAKE_MODEL.md) - Create models that use these table structures
- [`make:service`](MAKE_SERVICE.md) - Create services that interact with database
- [`make:dto`](MAKE_DTO.md) - Create DTOs for database data transfer
- [`php artisan migrate`](https://laravel.com/docs/migrations) - Run migrations
- [`php artisan migrate:rollback`](https://laravel.com/docs/migrations) - Rollback migrations

## Validation & Error Handling

The command includes several validation checks:

- **Module Existence**: Verifies the target module exists
- **File Conflicts**: Prevents overwriting without `--force` flag
- **Directory Creation**: Automatically creates the Database/Migrations directory
- **Input Validation**: Ensures all required parameters are provided
- **Sequence Management**: Prevents timestamp conflicts with automatic sequencing

## Examples by Use Case

### User Management Migrations
```bash
php artisan make:migration Auth create_users_table --create=auth_users
php artisan make:migration Auth create_profiles_table --create=auth_profiles
php artisan make:migration Auth create_roles_table --create=auth_roles
php artisan make:migration Auth create_permissions_table --create=auth_permissions
php artisan make:migration Auth create_user_roles_table --create=auth_user_roles
```

### E-commerce Migrations
```bash
php artisan make:migration Product create_categories_table --create=product_categories
php artisan make:migration Product create_items_table --create=product_items
php artisan make:migration Order create_carts_table --create=order_carts
php artisan make:migration Order create_payments_table --create=order_payments
php artisan make:migration Inventory create_stocks_table --create=inventory_stocks
```

### Content Management Migrations
```bash
php artisan make:migration Content create_articles_table --create=content_articles
php artisan make:migration Content create_comments_table --create=content_comments
php artisan make:migration Content create_tags_table --create=content_tags
php artisan make:migration Media create_files_table --create=media_files
```

### Data Modification Examples
```bash
# Add columns
php artisan make:migration Auth add_two_factor_to_users --table=auth_users
php artisan make:migration Product add_seo_fields_to_items --table=product_items

# Modify constraints
php artisan make:migration Auth update_email_constraints --table=auth_users
php artisan make:migration Order add_payment_indexes --table=order_payments

# Data migrations
php artisan make:migration Auth migrate_legacy_user_data
php artisan make:migration Product update_category_slugs
```

## Advanced Features

### 1. Foreign Key Relationships

Example of proper foreign key setup:
```php
public function up(): void
{
    Schema::create('auth_profiles', function (Blueprint $table) {
        $table->id();
        $table->uuid()->unique();
        
        // Foreign key to users table
        $table->uuid('user_uuid');
        $table->foreign('user_uuid')
              ->references('uuid')
              ->on('auth_users')
              ->onDelete('cascade');
              
        // Other columns...
    });
}
```

### 2. Composite Indexes

Create efficient composite indexes:
```php
$table->index(['user_uuid', 'is_active', 'created_at'], 'profiles_user_active_created_index');
$table->index(['email', 'is_active'], 'profiles_email_active_index');
```

### 3. JSON Column Usage

Leverage JSON columns for flexible data:
```php
$table->json('preferences')->nullable();
$table->json('metadata')->nullable();

// Add JSON indexes (MySQL 5.7+)
$table->index('preferences->theme', 'profiles_theme_index');
```

### 4. Full-Text Search

Set up full-text search indexes:
```php
$table->text('bio');
$table->fullText(['name', 'bio'], 'profiles_fulltext_index');
```

### 5. Conditional Migrations

Implement environment-specific logic:
```php
public function up(): void
{
    if (app()->environment('production')) {
        // Production-specific logic
    }
    
    if (Schema::hasTable('legacy_users')) {
        // Migration from legacy system
    }
}
```

This command streamlines database schema management while maintaining consistency with the established modular architecture patterns throughout your application.
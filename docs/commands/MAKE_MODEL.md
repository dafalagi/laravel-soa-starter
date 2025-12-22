# Make Model Command

The `make:model` command generates a new model class that extends BaseModel, following the modular architecture pattern used throughout the application.

## Command Signature

```bash
php artisan make:model {module} {model} [--force]
```

## Parameters

- **module** (required): The module name where the model will be created
- **model** (required): The model name (will be converted to PascalCase)
- **--force** (optional): Overwrite existing files without confirmation

## Usage Examples

### Basic Model Creation

```bash
# Create a basic model
php artisan make:model Auth Profile

# Create with force flag to overwrite existing files
php artisan make:model Auth User --force

# Create models for different modules
php artisan make:model Product Category
php artisan make:model Order Payment
php artisan make:model Content Article
```

### Generated Files Structure

When you run `php artisan make:model Auth Profile`, the command generates:

```
modules/Auth/Models/
└── Profile.php
```

## Generated Code Structure

### Model Class

**File:** `modules/Auth/Models/Profile.php`

```php
<?php

namespace Modules\Auth\Models;

use App\Models\BaseModel;

class Profile extends BaseModel
{
    protected $table = 'auth_profiles';

    /**
     * Get the relations that should restrict deletion.
     */
    private function getRestrictOnDeleteRelations(): array
    {
        return [];
    }

    // TODO: Add relationships, scopes, and other model methods
}
```

## Integration with Existing Architecture

### 1. BaseModel Inheritance

The generated models extend `App\Models\BaseModel` which provides:

- **Modular Factory Support**: Uses `HasModularFactory` trait for factory location
- **Soft Deletes**: Built-in soft delete functionality
- **Audit Fields**: Automatic created_by, updated_by, deleted_by tracking
- **UUID Support**: Hidden ID field with UUID primary key support
- **Timestamp Formatting**: Unix timestamp casting for API responses
- **Audit Relations**: Built-in methods for createdBy(), updatedBy(), deletedBy()

### 2. Table Naming Convention

Models automatically use a structured table naming pattern:
- Format: `{module_snake}_{pluralized_model_snake}`
- Examples:
  - `Auth Profile` → `auth_profiles`
  - `Product Category` → `product_categories`
  - `Order PaymentMethod` → `order_payment_methods`

### 3. Hidden Attributes

BaseModel automatically hides internal fields from JSON serialization:
```php
protected $hidden = [
    'id',           // Internal ID (use uuid instead)
    'is_active',    // Internal status flag
    'created_by',   // Audit field
    'updated_by',   // Audit field
    'deleted_by',   // Audit field
    'created_at',   // Use casted version
    'updated_at',   // Use casted version
    'deleted_at'    // Use casted version
];
```

### 4. Timestamp Casting

Timestamps are automatically cast to Unix timestamps for API responses:
```php
protected function casts(): array
{
    return [
        'created_at' => 'datetime:U',
        'updated_at' => 'datetime:U', 
        'deleted_at' => 'datetime:U',
    ];
}
```

## Customization Workflow

After generating the model, follow these steps to complete the implementation:

### 1. Define Fillable Attributes

Add the attributes that can be mass assigned:
```php
protected $fillable = [
    'uuid',
    'name',
    'email', 
    'bio',
    'avatar_url',
    'is_active',
];
```

### 2. Add Custom Casts

Define any additional attribute casting:
```php
protected $casts = [
    'preferences' => 'array',
    'birth_date' => 'date',
    'is_verified' => 'boolean',
];
```
*Note: This is overriding. So don't forget to redeclare what was declared in its parent*

### 3. Define Relationships

Add model relationships:
```php
public function user()
{
    return $this->belongsTo(User::class, 'user_uuid', 'uuid');
}

public function posts()
{
    return $this->hasMany(Post::class, 'author_uuid', 'uuid');
}

public function tags()
{
    return $this->belongsToMany(
        Tag::class, 
        'profile_tag', 
        'profile_uuid', 
        'tag_uuid',
        'uuid',
        'uuid'
    );
}
```

### 4. Add Scopes

Create query scopes for common filters:
```php
public function scopeActive($query)
{
    return $query->where('is_active', true);
}

public function scopeVerified($query)
{
    return $query->where('is_verified', true);
}

public function scopeByEmail($query, string $email)
{
    return $query->where('email', $email);
}
```

### 5. Configure Deletion Restrictions

Define relationships that should prevent deletion:
```php
private function getRestrictOnDeleteRelations(): array
{
    return [
        'posts',        // Cannot delete if user has posts
        'comments',     // Cannot delete if user has comments
    ];
}
```

### 6. Add Accessors and Mutators

Create attribute accessors and mutators:
```php
// Accessor
protected function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}

// Mutator  
protected function setEmailAttribute(string $value): void
{
    $this->attributes['email'] = strtolower($value);
}
```

## Factory Integration

Create a corresponding factory for your model:

```bash
# Create factory file
touch modules/Auth/database/factories/ProfileFactory.php
```

**File:** `modules/Auth/database/factories/ProfileFactory.php`

```php
<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\Profile;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'bio' => $this->faker->paragraph(),
            'avatar_url' => $this->faker->imageUrl(),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
```

## Migration Integration

Create a corresponding migration for your model:

```bash
# Create migration
php artisan make:migration create_auth_profiles_table
```

**Example Migration:**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auth_profiles', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('name');
            $table->string('email')->unique();
            $table->text('bio')->nullable();
            $table->string('avatar_url')->nullable();
            $table->boolean('is_active')->default(true);
            
            // Audit fields
            $table->uuid('created_by')->nullable();
            $table->uuid('updated_by')->nullable();
            $table->uuid('deleted_by')->nullable();
            $table->timestamp('created_at');
            $table->timestamp('updated_at');
            $table->timestamp('deleted_at')->nullable();
            
            $table->index(['uuid', 'is_active']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auth_profiles');
    }
};
```

## Best Practices

### 1. Naming Conventions
- Model names should be singular and descriptive
- Use PascalCase for class names
- Follow the pattern: `{Entity}` (e.g., `User`, `Profile`, `Category`)
- Table names are automatically pluralized and snake_cased

### 2. UUID vs ID Usage
- Use UUID for public-facing identifiers and relationships
- Keep ID hidden for internal database operations
- Always use UUID in API responses and relationships

### 3. Relationship Definitions
- Use UUIDs for foreign keys: `user_uuid`, `profile_uuid`
- Specify both local and foreign keys explicitly
- Use consistent naming across modules

### 4. Attribute Management
- Define fillable attributes explicitly
- Use appropriate casting for data types
- Hide sensitive or internal attributes

### 5. Query Optimization
- Add database indexes for commonly queried fields
- Use scopes for reusable query logic
- Implement eager loading for relationships

## Testing Integration

Create corresponding tests for your models:

```bash
# Create model test
touch modules/Auth/Tests/Unit/Models/ProfileTest.php
```

**Example Test:**

```php
<?php

namespace Modules\Auth\Tests\Unit\Models;

use Tests\TestCase;
use Modules\Auth\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_has_fillable_attributes(): void
    {
        $fillable = ['uuid', 'name', 'email', 'bio', 'avatar_url', 'is_active'];
        
        $this->assertEquals($fillable, (new Profile)->getFillable());
    }

    public function test_profile_uses_correct_table(): void
    {
        $this->assertEquals('auth_profiles', (new Profile)->getTable());
    }

    public function test_profile_can_be_created(): void
    {
        $profile = Profile::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);

        $this->assertDatabaseHas('auth_profiles', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
        ]);
    }
}
```

## Related Commands

- [`make:service`](MAKE_SERVICE.md) - Create services that interact with models
- [`make:dto`](MAKE_DTO.md) - Create DTOs for model data transfer
- [`make:controller`](MAKE_CONTROLLER.md) - Create controllers that use models
- [`make:request`](MAKE_REQUEST.md) - Create form requests for model validation
- [`make:migration`](https://laravel.com/docs/migrations) - Create database migrations

## Validation & Error Handling

The command includes several validation checks:

- **Module Existence**: Verifies the target module exists
- **File Conflicts**: Prevents overwriting without `--force` flag
- **Directory Creation**: Automatically creates the Models directory if it doesn't exist
- **Input Validation**: Ensures all required parameters are provided

## Examples by Use Case

### User Management Models
```bash
php artisan make:model Auth User
php artisan make:model Auth Profile
php artisan make:model Auth Role
php artisan make:model Auth Permission
```

### E-commerce Models
```bash
php artisan make:model Product Category
php artisan make:model Product Item
php artisan make:model Order Cart
php artisan make:model Order Payment
php artisan make:model Inventory Stock
```

### Content Management Models
```bash
php artisan make:model Content Article
php artisan make:model Content Comment
php artisan make:model Content Tag
php artisan make:model Media File
```

### Blog Models
```bash
php artisan make:model Blog Post
php artisan make:model Blog Category
php artisan make:model Blog Tag
php artisan make:model Blog Author
```

## Advanced Features

### 1. Soft Delete Handling

Models automatically support soft deletes through BaseModel:
```php
// Soft delete a model
$profile->delete();

// Restore a soft deleted model
$profile->restore();

// Force delete (permanent)
$profile->forceDelete();

// Query including soft deleted
Profile::withTrashed()->get();

// Query only soft deleted
Profile::onlyTrashed()->get();
```

### 2. Audit Trail

BaseModel provides automatic audit trail functionality:
```php
// Get who created the record
$creator = $profile->createdBy();

// Get who last updated the record
$updater = $profile->updatedBy();

// Get who deleted the record
$deleter = $profile->deletedBy();
```

### 3. Modular Factories

Models automatically locate factories in the correct module:
```php
// Factory automatically found in modules/Auth/database/factories/
$profile = Profile::factory()->create();

// Use factory states
$inactive_profile = Profile::factory()->inactive()->create();
```

This command streamlines the creation of model classes while maintaining consistency with the established modular architecture patterns throughout your application.
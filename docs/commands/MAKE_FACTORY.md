# Make Factory Command

The `make:factory` command generates new factory classes in the specified module, following the modular architecture pattern and integrating with the HasModularFactory trait for automatic factory discovery.

## Command Signature

```bash
php artisan make:factory {module} {model} [--force]
```

## Parameters

- **module** (required): The module name where the factory will be created
- **model** (required): The model name for which to create the factory
- **--force** (optional): Overwrite existing files without confirmation

## Usage Examples

### Basic Factory Creation

```bash
# Create a factory for the User model in Auth module
php artisan make:factory Auth User

# Create a factory with force flag to overwrite existing
php artisan make:factory Auth User --force
```

### Generated Files Structure

When you run `php artisan make:factory Auth Profile`, the command generates:

```
modules/Auth/Database/Factories/
└── ProfileFactory.php
```

## Generated Code Structure

**Command:** `php artisan make:factory Auth Profile`

**File:** `modules/Auth/Database/Factories/ProfileFactory.php`

```php
<?php

namespace Modules\Auth\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Auth\Models\Profile;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Profile::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => fake()->uuid(),

            // TODO: Add your model's factory attributes here
            
            'is_active' => true,
            'version' => 0,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'created_at' => time(),
            'updated_at' => null,
            'deleted_at' => null,
        ];
    }

    // TODO: Add additional state modifiers as needed
}
```

## Integration with HasModularFactory Trait

### 1. Automatic Factory Discovery

When your model uses the `HasModularFactory` trait, Laravel automatically finds the factory:

```php
// In your model (e.g., Modules\Auth\Models\Profile)
use App\Traits\HasModularFactory;

class Profile extends BaseModel
{
    use HasModularFactory;
    
    // Your model code...
}
```

### 2. Factory Usage

```php
// Create a single model instance
$profile = Profile::factory()->create();

// Create multiple instances
$profiles = Profile::factory(5)->create();

// Make without persisting to database
$profile = Profile::factory()->make();

// Create with specific attributes
$profile = Profile::factory()->create([
    'name' => 'John Doe',
    'email' => 'john@example.com'
]);
```

### 3. Factory Location Resolution

The trait automatically resolves:
- **Model**: `Modules\Auth\Models\Profile`
- **Factory**: `Modules\Auth\Database\Factories\ProfileFactory`

## Customization Workflow

After generating the factory, follow these steps to complete the implementation:

### 1. Add Model-Specific Attributes

Replace the TODO comment with your model's actual attributes:

```php
public function definition(): array
{
    return [
        'uuid' => fake()->uuid(),

        // Profile-specific attributes
        'name' => fake()->name(),
        'email' => fake()->unique()->safeEmail(),
        'bio' => fake()->paragraph(),
        'avatar_url' => fake()->imageUrl(200, 200),
        'birth_date' => fake()->date(),
        'phone' => fake()->phoneNumber(),
        'website' => fake()->url(),
        'location' => fake()->city() . ', ' . fake()->country(),
        
        // JSON fields
        'preferences' => [
            'theme' => fake()->randomElement(['light', 'dark']),
            'language' => fake()->languageCode(),
            'notifications' => fake()->boolean(),
        ],
        'social_links' => [
            'twitter' => '@' . fake()->userName(),
            'linkedin' => fake()->url(),
            'github' => fake()->url(),
        ],
        
        // Foreign keys (if applicable)
        'user_uuid' => User::factory(),
        
        // Standard audit fields
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

### 2. Add State Modifiers

Create factory states for different scenarios:

```php
/**
 * Indicate that the profile is inactive.
 */
public function inactive(): static
{
    return $this->state(fn (array $attributes) => [
        'is_active' => false,
    ]);
}

/**
 * Indicate that the profile is verified.
 */
public function verified(): static
{
    return $this->state(fn (array $attributes) => [
        'email_verified_at' => time(),
        'is_verified' => true,
    ]);
}

/**
 * Create a profile with premium features.
 */
public function premium(): static
{
    return $this->state(fn (array $attributes) => [
        'plan_type' => 'premium',
        'preferences' => array_merge(
            $attributes['preferences'] ?? [],
            ['premium_features' => true]
        ),
    ]);
}

/**
 * Create a profile for a specific user.
 */
public function forUser(User $user): static
{
    return $this->state(fn (array $attributes) => [
        'user_uuid' => $user->uuid,
        'created_by' => $user->id,
    ]);
}

/**
 * Create a profile with social media links.
 */
public function withSocialMedia(): static
{
    return $this->state(fn (array $attributes) => [
        'social_links' => [
            'twitter' => 'https://twitter.com/' . fake()->userName(),
            'linkedin' => 'https://linkedin.com/in/' . fake()->userName(),
            'github' => 'https://github.com/' . fake()->userName(),
            'instagram' => 'https://instagram.com/' . fake()->userName(),
        ],
    ]);
}
```

### 3. Add Relationship Factories

Handle model relationships:

```php
/**
 * Configure factory after making.
 */
public function configure(): static
{
    return $this->afterMaking(function (Profile $profile) {
        // Set up relationships or additional logic after making
    })->afterCreating(function (Profile $profile) {
        // Set up relationships or additional logic after creating
        
        // Example: Create related models
        if (!$profile->user_uuid) {
            $user = User::factory()->create();
            $profile->update(['user_uuid' => $user->uuid]);
        }
        
        // Example: Create profile settings
        ProfileSetting::factory()->create([
            'profile_uuid' => $profile->uuid,
        ]);
    });
}
```

## Advanced Usage Examples

### 1. Factory with Complex Data Types

```php
public function definition(): array
{
    return [
        'uuid' => fake()->uuid(),
        
        // Complex JSON structures
        'preferences' => [
            'notification_settings' => [
                'email' => fake()->boolean(),
                'push' => fake()->boolean(),
                'sms' => fake()->boolean(),
            ],
            'privacy_settings' => [
                'profile_visibility' => fake()->randomElement(['public', 'private', 'friends']),
                'show_email' => fake()->boolean(),
                'show_phone' => fake()->boolean(),
            ],
            'ui_settings' => [
                'theme' => fake()->randomElement(['light', 'dark', 'auto']),
                'language' => fake()->randomElement(['en', 'es', 'fr', 'de']),
                'timezone' => fake()->timezone(),
            ],
        ],
        
        // Calculated fields
        'display_name' => fn (array $attributes) => 
            $attributes['first_name'] . ' ' . $attributes['last_name'],
            
        // Conditional fields
        'email_verified_at' => fake()->boolean(80) ? time() : null,
        
        // Date ranges
        'birth_date' => fake()->dateTimeBetween('-80 years', '-18 years')->format('Y-m-d'),
        
        // Standard fields...
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

### 2. Factory with File Uploads

```php
use Illuminate\Http\UploadedFile;

public function definition(): array
{
    return [
        'uuid' => fake()->uuid(),
        
        // File uploads (for testing)
        'avatar_path' => fn () => UploadedFile::fake()->image('avatar.jpg')->store('avatars'),
        'resume_path' => fn () => UploadedFile::fake()->create('resume.pdf', 100)->store('resumes'),
        
        // Or just file paths for existing files
        'avatar_url' => fake()->imageUrl(400, 400, 'people'),
        
        // Standard fields...
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

### 3. Sequence-based Factories

```php
use Illuminate\Database\Eloquent\Factories\Sequence;

public function definition(): array
{
    return [
        'uuid' => fake()->uuid(),
        'email' => fake()->unique()->safeEmail(),
        'name' => fake()->name(),
        
        // Standard fields...
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}

// Usage with sequences
Profile::factory(10)
    ->sequence(
        ['plan_type' => 'basic'],
        ['plan_type' => 'premium'],
        ['plan_type' => 'enterprise'],
    )
    ->create();
```

## Best Practices

### 1. Data Consistency

```php
public function definition(): array
{
    $firstName = fake()->firstName();
    $lastName = fake()->lastName();
    
    return [
        'uuid' => fake()->uuid(),
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => strtolower($firstName . '.' . $lastName . '@' . fake()->safeEmailDomain()),
        'display_name' => $firstName . ' ' . $lastName,
        'username' => strtolower($firstName . $lastName . fake()->numberBetween(1, 999)),
        
        // Consistent data relationships
        'birth_date' => $birthDate = fake()->dateTimeBetween('-80 years', '-18 years'),
        'age' => now()->diffInYears($birthDate),
        
        // Standard fields...
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

### 2. Realistic Test Data

```php
public function definition(): array
{
    return [
        'uuid' => fake()->uuid(),
        
        // Use realistic constraints
        'phone' => fake()->regexify('\+1[0-9]{10}'), // US phone format
        'postal_code' => fake()->regexify('[0-9]{5}'), // US zip code
        'website' => fake()->optional(0.3)->url(), // 30% have websites
        
        // Weighted random values
        'plan_type' => fake()->randomElement([
            'basic', 'basic', 'basic', 'basic', // 40% basic
            'premium', 'premium', 'premium',    // 30% premium
            'enterprise', 'enterprise',         // 20% enterprise
            'trial'                             // 10% trial
        ]),
        
        // Realistic email domains
        'email' => fake()->userName() . '@' . fake()->randomElement([
            'gmail.com', 'yahoo.com', 'hotmail.com', 'outlook.com', 'company.com'
        ]),
        
        // Standard fields...
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

### 3. Localization Support

```php
public function definition(): array
{
    $locale = fake()->randomElement(['en_US', 'es_ES', 'fr_FR', 'de_DE']);
    fake()->locale($locale);
    
    return [
        'uuid' => fake()->uuid(),
        'name' => fake()->name(),
        'address' => fake()->address(),
        'phone' => fake()->phoneNumber(),
        'locale' => $locale,
        
        // Localized content
        'bio' => fake()->paragraph(),
        'company' => fake()->company(),
        'job_title' => fake()->jobTitle(),
        
        // Standard fields...
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

## Testing Integration

### 1. Basic Factory Testing

Create tests to verify factory functionality:

```php
<?php

namespace Modules\Auth\Tests\Feature\Factories;

use Tests\TestCase;
use Modules\Auth\Models\Profile;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProfileFactoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_profile_with_factory(): void
    {
        $profile = Profile::factory()->create();

        $this->assertDatabaseHas('auth_profiles', [
            'uuid' => $profile->uuid,
            'is_active' => true,
        ]);
    }

    public function test_factory_creates_valid_data(): void
    {
        $profile = Profile::factory()->make();

        $this->assertNotEmpty($profile->uuid);
        $this->assertNotEmpty($profile->name);
        $this->assertNotEmpty($profile->email);
        $this->assertTrue($profile->is_active);
        $this->assertEquals(0, $profile->version);
    }

    public function test_can_use_factory_states(): void
    {
        $inactiveProfile = Profile::factory()->inactive()->create();
        $premiumProfile = Profile::factory()->premium()->create();

        $this->assertFalse($inactiveProfile->is_active);
        $this->assertEquals('premium', $premiumProfile->plan_type);
    }

    public function test_factory_handles_relationships(): void
    {
        $profile = Profile::factory()->create();

        $this->assertNotNull($profile->user_uuid);
        $this->assertInstanceOf(\Modules\Auth\Models\User::class, $profile->user);
    }
}
```

### 2. Seeder Integration

Use factories in database seeders:

```php
<?php

namespace Modules\Auth\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Auth\Models\Profile;
use Modules\Auth\Models\User;

class ProfileSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user with profile
        $adminUser = User::factory()->admin()->create([
            'email' => 'admin@example.com'
        ]);
        
        Profile::factory()->verified()->create([
            'user_uuid' => $adminUser->uuid,
            'name' => 'System Administrator',
            'email' => $adminUser->email,
        ]);

        // Create regular users with profiles
        User::factory(50)
            ->has(Profile::factory()->withSocialMedia(), 'profile')
            ->create();

        // Create premium users
        User::factory(10)
            ->has(Profile::factory()->premium()->verified(), 'profile')
            ->create();
    }
}
```

### 3. Feature Test Usage

Use factories in feature tests:

```php
public function test_user_can_update_profile(): void
{
    $user = User::factory()->create();
    $profile = Profile::factory()->forUser($user)->create();

    $response = $this->actingAs($user)->put("/profiles/{$profile->uuid}", [
        'name' => 'Updated Name',
        'bio' => 'Updated bio',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('auth_profiles', [
        'uuid' => $profile->uuid,
        'name' => 'Updated Name',
        'bio' => 'Updated bio',
    ]);
}
```

## Factory Examples by Use Case

### User Management Factories

```bash
# Create user-related factories
php artisan make:factory Auth User
php artisan make:factory Auth Profile
php artisan make:factory Auth Role
php artisan make:factory Auth Permission
php artisan make:factory Auth UserRole
```

### E-commerce Factories

```bash
# Create product-related factories
php artisan make:factory Product Category
php artisan make:factory Product Item
php artisan make:factory Product Review
php artisan make:factory Order Cart
php artisan make:factory Order Payment
php artisan make:factory Inventory Stock
```

### Content Management Factories

```bash
# Create content-related factories
php artisan make:factory Content Article
php artisan make:factory Content Comment
php artisan make:factory Content Tag
php artisan make:factory Media File
php artisan make:factory Media Image
```

## Validation & Error Handling

The command includes several validation checks:

- **Module Existence**: Verifies the target module exists
- **Model Warning**: Warns if the target model doesn't exist yet
- **File Conflicts**: Prevents overwriting without `--force` flag
- **Directory Creation**: Automatically creates the Database/Factories directory
- **Input Validation**: Ensures all required parameters are provided

## Common Patterns

### 1. Factory with Traits

```php
class ProfileFactory extends Factory
{
    use HasAuditFields, HasUuidField;
    
    protected $model = Profile::class;

    public function definition(): array
    {
        return array_merge(
            $this->getAuditFields(),
            $this->getUuidField(),
            [
                'name' => fake()->name(),
                'email' => fake()->unique()->safeEmail(),
                // ... other fields
            ]
        );
    }
}
```

### 2. Factory with Enums

```php
use App\Enums\PlanType;
use App\Enums\ProfileStatus;

public function definition(): array
{
    return [
        'uuid' => fake()->uuid(),
        'plan_type' => fake()->randomElement(PlanType::cases()),
        'status' => ProfileStatus::ACTIVE,
        'priority_level' => fake()->numberBetween(1, 5),
        
        // Standard audit fields...
        'is_active' => true,
        'version' => 0,
        'created_by' => null,
        'updated_by' => null,
        'deleted_by' => null,
        'created_at' => time(),
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

### 3. Factory with Custom Providers

```php
use Faker\Provider\Base;

class ProfileFactory extends Factory
{
    protected $model = Profile::class;

    public function definition(): array
    {
        // Register custom faker provider
        fake()->addProvider(new class(fake()) extends Base {
            public function profilePlan(): string
            {
                return $this->randomElement(['starter', 'professional', 'enterprise']);
            }
        });

        return [
            'uuid' => fake()->uuid(),
            'plan_type' => fake()->profilePlan(),
            
            // Standard audit fields...
            'is_active' => true,
            'version' => 0,
            'created_by' => null,
            'updated_by' => null,
            'deleted_by' => null,
            'created_at' => time(),
            'updated_at' => null,
            'deleted_at' => null,
        ];
    }
}
```

## Performance Considerations

### 1. Efficient Factory Usage

```php
// Efficient: Create multiple records in batch
Profile::factory(100)->create();

// Inefficient: Create records one by one
for ($i = 0; $i < 100; $i++) {
    Profile::factory()->create();
}
```

### 2. Memory Management

```php
// For large datasets, use chunks
User::factory(10000)->create();

// Better: Process in chunks for memory efficiency
collect(range(1, 100))->each(function () {
    User::factory(100)->create();
});
```

### 3. Database Optimization

```php
// Disable model events for better performance
Profile::factory(1000)->createQuietly();

// Use raw inserts for bulk data
Profile::factory(1000)->make()->each(function ($profile) {
    DB::table('auth_profiles')->insert($profile->toArray());
});
```

## Related Commands

- [`make:model`](MAKE_MODEL.md) - Create models that use these factories
- [`make:migration`](MAKE_MIGRATION.md) - Create tables for factory-generated data
- [`make:seeder`](MAKE_SEEDER.md) - Create seeders that use these factories
- [`php artisan db:seed`](https://laravel.com/docs/seeding) - Run seeders with factory data
- [`php artisan tinker`](https://laravel.com/docs/artisan#tinker) - Test factories interactively

## Advanced Integration

### 1. Custom Factory Command

Extend the base command for specialized factory generation:

```bash
php artisan make:factory Auth User --with-states --with-relationships
```

### 2. Factory Templates

Create reusable factory templates for common patterns:

```bash
php artisan make:factory Auth Profile --template=social-profile
php artisan make:factory Product Item --template=e-commerce-item
```

### 3. Factory Validation

Add validation to ensure factory-generated data meets business rules:

```php
public function definition(): array
{
    return [
        'uuid' => fake()->uuid(),
        'email' => $this->ensureUniqueEmail(),
        'phone' => $this->ensureValidPhone(),
        // ... other fields
    ];
}

private function ensureUniqueEmail(): string
{
    do {
        $email = fake()->unique()->safeEmail();
    } while (Profile::where('email', $email)->exists());
    
    return $email;
}
```

This command streamlines test data generation while maintaining consistency with the established modular architecture patterns throughout your application.
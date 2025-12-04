# Make Test Command

The `make:test` command generates test files for your modules, supporting both feature and unit tests with optional service-specific test generation.

## Command Signature

```bash
php artisan make:test {module} {feature} {name} {type} [--service] [--force]
```

## Parameters

- **module** (required): The module name where the test will be created
- **feature** (required): The feature name that groups related tests
- **name** (required): The test name (will automatically append "Test" if not present)
- **type** (required): The test type - either "feature" or "unit"
- **--service** (optional): Generate test for service classes
- **--force** (optional): Overwrite existing files without confirmation

## Usage Examples

### Basic Test Creation

```bash
# Create feature tests
php artisan make:test Auth User CreateUser feature
php artisan make:test Auth User UpdateUser feature

# Create unit tests
php artisan make:test Auth User UserModel unit
php artisan make:test Product Category CategoryModel unit
```

### Service Test Creation

```bash
# Create service feature tests
php artisan make:test Auth User CreateUserService feature --service
php artisan make:test Order Payment ProcessPaymentService feature --service

# Create service unit tests
php artisan make:test Auth User StoreUserService unit --service
php artisan make:test Product Category CreateCategoryService unit --service
```

### Force Overwrite

```bash
# Overwrite existing test files
php artisan make:test Auth User CreateUser feature --force
php artisan make:test Auth User CreateUserService unit --service --force
```

## Generated File Structure

### Regular Tests

#### Feature Tests
```
modules/{Module}/Tests/Feature/{Feature}/{TestName}.php
```

**Example:**
```bash
php artisan make:test Auth User CreateUser feature
# Creates: modules/Auth/Tests/Feature/User/CreateUserTest.php
```

#### Unit Tests
```
modules/{Module}/Tests/Unit/{Feature}/{TestName}.php
```

**Example:**
```bash
php artisan make:test Auth User UserModel unit
# Creates: modules/Auth/Tests/Unit/User/UserModelTest.php
```

### Service Tests (with --service flag)

#### Service Feature Tests
```
modules/{Module}/Tests/Feature/Services/{Feature}/{TestName}.php
```

**Example:**
```bash
php artisan make:test Auth User CreateUserService feature --service
# Creates: modules/Auth/Tests/Feature/Services/User/CreateUserServiceTest.php
```

#### Service Unit Tests
```
modules/{Module}/Tests/Unit/Services/{Feature}/{TestName}.php
```

**Example:**
```bash
php artisan make:test Auth User StoreUserService unit --service
# Creates: modules/Auth/Tests/Unit/Services/User/StoreUserServiceTest.php
```

## Generated Code Templates

### Feature Test Template

**File:** `modules/Auth/Tests/Feature/User/CreateUserTest.php`

```php
<?php

namespace Modules\Auth\Tests\Feature\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CreateUserTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
    }

    // TODO: Implement feature tests
}
```

### Unit Test Template (Regular)

**File:** `modules/Auth/Tests/Unit/User/UserModelTest.php`

```php
<?php

namespace Modules\Auth\Tests\Unit\User;

use PHPUnit\Framework\TestCase;

class UserModelTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
    }

    // TODO: Implement unit tests
}
```

### Unit Test Template (Service)

**File:** `modules/Auth/Tests/Unit/Services/User/StoreUserServiceTest.php`

```php
<?php

namespace Modules\Auth\Tests\Unit\Services\User;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StoreUserServiceTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        // Additional setup if needed
    }

    // TODO: Implement unit tests
}
```

## Test Type Differences

### Feature Tests
- **Purpose**: Test complete features and user workflows
- **Scope**: End-to-end testing, HTTP requests, database interactions
- **Base Class**: `Tests\TestCase` (Laravel's TestCase)
- **Traits**: `RefreshDatabase` for database testing
- **Use Cases**:
  - API endpoint testing
  - User authentication flows  
  - Complete business workflows
  - Service integration testing

### Unit Tests

#### Regular Unit Tests
- **Purpose**: Test individual classes and methods in isolation
- **Scope**: Single class or method testing
- **Base Class**: `PHPUnit\Framework\TestCase` (Pure PHPUnit)
- **Traits**: No database-related traits by default
- **Use Cases**:
  - Model method testing
  - Helper function testing
  - Utility class testing
  - Pure logic testing

#### Service Unit Tests
- **Purpose**: Test service classes with minimal dependencies
- **Scope**: Service method testing with database access
- **Base Class**: `Tests\TestCase` (Laravel's TestCase)
- **Traits**: `RefreshDatabase` for service testing
- **Use Cases**:
  - Service logic testing
  - Service validation testing
  - Service method isolation testing
  - Business logic testing

## Integration with Testing Workflow

### 1. Complete Test Suite Creation

```bash
# Create full test suite for a feature
php artisan make:test Auth User CreateUser feature
php artisan make:test Auth User CreateUserService feature --service
php artisan make:test Auth User CreateUserService unit --service
php artisan make:test Auth User UserModel unit
```

### 2. Test Organization

The generated structure organizes tests logically:

```
modules/Auth/Tests/
├── Feature/
│   ├── Services/
│   │   └── User/
│   │       ├── CreateUserServiceTest.php
│   │       └── UpdateUserServiceTest.php
│   └── User/
│       ├── CreateUserTest.php
│       └── UpdateUserTest.php
└── Unit/
    ├── Services/
    │   └── User/
    │       ├── CreateUserServiceTest.php
    │       └── StoreUserServiceTest.php
    └── User/
        ├── UserModelTest.php
        └── UserHelperTest.php
```

### 3. Running Tests

```bash
# Run all tests for a module
vendor/bin/phpunit modules/Auth/Tests/

# Run specific test type
vendor/bin/phpunit modules/Auth/Tests/Feature/
vendor/bin/phpunit modules/Auth/Tests/Unit/

# Run service tests only
vendor/bin/phpunit modules/Auth/Tests/Feature/Services/
vendor/bin/phpunit modules/Auth/Tests/Unit/Services/

# Run specific test file
vendor/bin/phpunit modules/Auth/Tests/Feature/User/CreateUserTest.php
```

## Best Practices

### 1. Test Naming Conventions
- **Descriptive Names**: Use clear, descriptive test names
- **Feature Tests**: Name after the feature or workflow being tested
- **Unit Tests**: Name after the class or method being tested
- **Service Tests**: Include "Service" in the name for clarity

### 2. Test Organization
- **Group Related Tests**: Use the feature parameter to group related tests
- **Separate Concerns**: Keep feature and unit tests separate
- **Service Isolation**: Use service tests for business logic testing

### 3. Test Implementation Examples

#### Feature Test Implementation
```php
public function test_user_can_be_created_successfully(): void
{
    // Arrange
    $user_data = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ];

    // Act
    $response = $this->postJson('/api/v0/admin/auth/register', $user_data);

    // Assert
    $response->assertStatus(201);
    $response->assertJsonStructure([
        'success',
        'data' => [
            'id',
            'name',
            'email',
        ],
        'message'
    ]);
    
    $this->assertDatabaseHas('users', [
        'email' => 'john@example.com'
    ]);
}
```

#### Service Unit Test Implementation
```php
public function test_store_user_service_creates_user_successfully(): void
{
    // Arrange
    $dto = new StoreUserRequestDTO([
        'name' => 'Jane Doe',
        'email' => 'jane@example.com',
        'password' => 'password',
    ]);
    
    $service = app(StoreUserServiceInterface::class);

    // Act
    $result = $service->execute($dto);

    // Assert
    $this->assertTrue($result['success']);
    $this->assertArrayHasKey('data', $result);
    $this->assertInstanceOf(UserResponseDTO::class, $result['data']);
    $this->assertDatabaseHas('users', [
        'email' => 'jane@example.com'
    ]);
}
```

## Validation & Error Handling

The command includes comprehensive validation:

- **Module Existence**: Verifies the target module exists
- **Test Type Validation**: Ensures type is either 'feature' or 'unit'
- **File Conflict Prevention**: Prevents overwriting without `--force` flag
- **Directory Creation**: Automatically creates necessary directories
- **Parameter Requirements**: Validates all required parameters are provided

## Testing Strategy Recommendations

### 1. Feature Tests
- Test complete user workflows
- Test API endpoints end-to-end
- Test authentication and authorization
- Test data persistence and retrieval
- Test error scenarios and edge cases

### 2. Unit Tests (Regular)
- Test model methods and relationships
- Test helper functions and utilities
- Test data transformations
- Test validation logic
- Test business rules in isolation

### 3. Unit Tests (Service)
- Test service methods in isolation
- Test service validation rules
- Test service data preparation
- Test service business logic
- Test service error handling

## Related Commands

- [`make:service`](MAKE_SERVICE.md) - Create services to test
- [`make:controller`](MAKE_CONTROLLER.md) - Create controllers to test
- [`make:request`](MAKE_REQUEST.md) - Create requests to test
- [`make:dto`](MAKE_DTO.md) - Create DTOs for test data

## Examples by Use Case

### Authentication Module Tests
```bash
# Feature tests for authentication workflows
php artisan make:test Auth User LoginWorkflow feature
php artisan make:test Auth User RegisterWorkflow feature
php artisan make:test Auth User LogoutWorkflow feature

# Service tests for authentication services  
php artisan make:test Auth User LoginService feature --service
php artisan make:test Auth User RegisterService unit --service

# Model tests for User model
php artisan make:test Auth User UserModel unit
```

### E-commerce Module Tests
```bash
# Product feature tests
php artisan make:test Product Category CreateCategory feature
php artisan make:test Product Inventory UpdateStock feature

# Order service tests
php artisan make:test Order Cart AddToCartService unit --service
php artisan make:test Order Payment ProcessPaymentService feature --service

# Model tests
php artisan make:test Product Category CategoryModel unit
php artisan make:test Order Payment PaymentModel unit
```

### Content Management Tests
```bash
# Content feature tests
php artisan make:test Content Article PublishArticle feature
php artisan make:test Content Media UploadMedia feature

# Content service tests
php artisan make:test Content Article CreateArticleService unit --service
php artisan make:test Content Media ProcessMediaService feature --service
```

This command provides a comprehensive testing foundation that integrates seamlessly with your modular SOA architecture, supporting both traditional and service-oriented testing approaches.
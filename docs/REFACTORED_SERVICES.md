# Refactored Services Structure

## Overview

The Auth module services have been refactored to follow the Single Responsibility Principle (SRP) and improved architecture patterns. Each service now handles one specific action and includes comprehensive validation.

## New Service Structure

### Folder Organization
```
modules/Auth/Services/
├── Auth/                          # Authentication-related services
│   ├── Contracts/
│   │   ├── LoginServiceInterface.php
│   │   ├── LogoutServiceInterface.php
│   │   ├── RefreshTokenServiceInterface.php
│   │   └── RegisterServiceInterface.php
│   ├── LoginService.php
│   ├── LogoutService.php
│   ├── RefreshTokenService.php
│   └── RegisterService.php
├── User/                          # User management services
│   ├── Contracts/
│   │   └── GetCurrentUserServiceInterface.php
│   └── GetCurrentUserService.php
├── AuthService.php               # Legacy service (kept for backward compatibility)
└── Contracts/
    └── AuthServiceInterface.php  # Legacy contract
```

## Service Design Principles

### ✅ **Single Responsibility Principle**
Each service handles exactly one action:
- `RegisterService` → User registration only
- `LoginService` → User authentication only
- `LogoutService` → User logout only
- `RefreshTokenService` → Token refresh only
- `GetCurrentUserService` → Retrieve current user only

### ✅ **Validation Moved to Services**
- Controller validation removed
- Services now handle their own validation via `prepare()` methods
- Comprehensive input validation and business logic validation
- Proper error handling with ValidationException

### ✅ **Consistent Method Interface**
All services implement a consistent `execute()` method pattern:
```php
public function execute(DTO $dto): ResponseDTO
```

### ✅ **Dependency Injection Ready**
- All services have contracts/interfaces
- Properly bound in service provider
- Easy to mock for testing
- Follows Laravel's service container patterns

## Service Details

### 1. RegisterService
**Purpose**: Handle user registration with comprehensive validation

**Features**:
- Input validation (required fields, email format, password strength)
- Business logic validation (unique email, password confirmation)
- User creation with password hashing
- Returns AuthResponseDTO with user data

**Usage**:
```php
$dto = RegisterRequestDTO::fromArray($request->all());
$response = $this->register_service->execute($dto);
```

### 2. LoginService
**Purpose**: Handle user authentication

**Features**:
- Credential validation
- Authentication attempt
- Session management
- Returns AuthResponseDTO with user data

**Usage**:
```php
$dto = LoginRequestDTO::fromArray($request->all());
$response = $this->login_service->execute($dto);
```

### 3. LogoutService
**Purpose**: Handle user logout

**Features**:
- Authentication check
- Session invalidation
- Cleanup operations

**Usage**:
```php
$this->logout_service->execute();
```

### 4. RefreshTokenService
**Purpose**: Handle authentication token refresh

**Features**:
- User authentication verification
- Account status validation
- Token regeneration
- Returns AuthResponseDTO with refreshed data

**Usage**:
```php
$response = $this->refresh_token_service->execute();
```

### 5. GetCurrentUserService
**Purpose**: Retrieve authenticated user information

**Features**:
- Authentication verification
- Account status validation
- User data formatting
- Returns UserResponseDTO or null

**Usage**:
```php
$user = $this->get_current_user_service->execute();
```

## Prepare Methods

Each service implements a private `prepare()` method for validation:

```php
/**
 * Prepare and validate the service data.
 */
private function prepare(RequestDTO $dto): void
{
    // 1. Input validation using Laravel Validator
    $validator = Validator::make(/* data */, /* rules */);
    
    if ($validator->fails()) {
        throw new ValidationException($validator);
    }
    
    // 2. Business logic validation
    // Custom validation rules, database checks, etc.
    
    // 3. Additional preparation
    // Data transformation, permission checks, etc.
}
```

## Controller Integration

Updated AuthController to use individual services:

```php
class AuthController extends Controller
{
    public function __construct(
        private readonly RegisterServiceInterface $register_service,
        private readonly LoginServiceInterface $login_service,
        private readonly LogoutServiceInterface $logout_service,
        private readonly RefreshTokenServiceInterface $refresh_token_service,
        private readonly GetCurrentUserServiceInterface $get_current_user_service
    ) {}
    
    public function register(Request $request): JsonResponse
    {
        try {
            $dto = RegisterRequestDTO::fromArray($request->all());
            $response = $this->register_service->execute($dto);
            
            return $this->successResponse(
                'User registered successfully',
                $response->toArray(),
                201
            );
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        }
    }
    
    // ... other methods follow the same pattern
}
```

## Service Provider Configuration

Updated AuthServiceProvider to bind all individual services:

```php
public function register(): void
{
    // Register individual auth services
    $this->app->bind(RegisterServiceInterface::class, RegisterService::class);
    $this->app->bind(LoginServiceInterface::class, LoginService::class);
    $this->app->bind(LogoutServiceInterface::class, LogoutService::class);
    $this->app->bind(RefreshTokenServiceInterface::class, RefreshTokenService::class);
    
    // Register user management services
    $this->app->bind(GetCurrentUserServiceInterface::class, GetCurrentUserService::class);
    
    // Keep legacy service for backward compatibility
    $this->app->bind(AuthServiceInterface::class, AuthService::class);
}
```

## Testing Strategy

### Unit Testing Individual Services
Each service has dedicated unit tests:
- `RegisterServiceTest` → Tests registration logic and validation
- `LoginServiceTest` → Tests authentication logic
- And so on...

### Feature Testing Controller Integration
Existing feature tests verify controller → service integration works correctly.

### Benefits Achieved

1. **Better Separation of Concerns**: Each service has a single, well-defined purpose
2. **Improved Testability**: Services can be tested in isolation
3. **Enhanced Maintainability**: Changes to one service don't affect others
4. **Validation Centralization**: All validation logic is in the appropriate service
5. **Consistent Architecture**: All services follow the same patterns
6. **Dependency Injection**: Easy to mock and replace services for testing
7. **Future Extensibility**: Easy to add new services following the same pattern

## Migration Path

- ✅ Legacy `AuthService` kept for backward compatibility
- ✅ All existing tests pass without modification
- ✅ Controller updated to use new services
- ✅ Service provider configured for dependency injection
- ✅ New service tests added for comprehensive coverage

The refactoring provides a solid foundation for future module development while maintaining full backward compatibility.
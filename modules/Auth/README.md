# Auth Module

The Auth module provides comprehensive authentication and user management functionality for the Laravel SOA Starter application. It follows the Service-Oriented Architecture (SOA) pattern with clear separation of concerns through DTOs, services, and controllers.

## 📋 Overview

The Auth module handles all authentication-related operations including user login, logout, token refresh, and user management (CRUD operations). It is built on a foundation of service-oriented principles with strict type safety through Data Transfer Objects (DTOs).

### Key Principles
- **Single Responsibility**: Each service handles one specific authentication action
- **Type Safety**: DTOs ensure type-safe data transfer across layers
- **Extensibility**: Easy to extend with new authentication methods or strategies
- **Testability**: Comprehensive service layer makes testing straightforward

## 🚀 Features

### Authentication Services
- **User Login** - Authenticate users and generate API tokens
- **User Logout** - Revoke user tokens and clean up sessions
- **Token Refresh** - Refresh expired authentication tokens
- **Password Validation** - Secure password verification with hashing

### User Management Services
- **Get Users** - Retrieve user(s) with pagination and sorting
- **Create Users** - Store new users in the system
- **Update Users** - Modify existing user information
- **Delete Users** - Soft delete users with audit trails

### Features
- OAuth 2.0 API token authentication via Laravel Passport
- User soft deletes for data preservation
- Comprehensive validation at service layer
- Multi-client support (Admin, Web, Mobile)
- Notification support for user events
- Database factories for testing

## 📁 Directory Structure

```
Auth/
├── DTOs/                           # Data Transfer Objects
│   ├── LoginRequestDTO.php         # Login request validation
│   ├── AuthResponseDTO.php         # Authentication response
│   └── User/
│       ├── Requests/               # User request DTOs
│       └── Responses/              # User response DTOs
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php      # Authentication endpoints
│   │   └── Api/
│   │       └── Admin/              # Admin-specific controllers
│   ├── Requests/                   # Form request validation
│   └── Resources/                  # API resource transformers
├── Models/
│   └── User.php                    # Eloquent User model
├── Services/
│   ├── Auth/                       # Authentication services
│   │   ├── Contracts/              # Service interfaces
│   │   ├── LoginService.php        # User login logic
│   │   ├── LogoutService.php       # User logout logic
│   │   └── RefreshTokenService.php # Token refresh logic
│   └── User/                       # User management services
│       ├── Contracts/              # Service interfaces
│       ├── GetUserService.php      # Retrieve users
│       ├── StoreUserService.php    # Create users
│       ├── UpdateUserService.php   # Update users
│       └── DeleteUserService.php   # Delete users
├── Routes/
│   ├── api.php                     # Route registration
│   └── admin/
│       └── admin.php               # Admin authentication routes
├── Database/
│   ├── Migrations/                 # Database migrations
│   ├── Factories/                  # Model factories for testing
│   └── Seeders/                    # Database seeders
├── Providers/
│   └── AuthModuleServiceProvider.php # Module service provider
├── Resources/
│   └── lang/                       # Translation files
├── Tests/
│   └── Unit/                       # Unit tests
└── README.md                       # This file
```

## 🔌 Service Architecture

### Authentication Services

#### LoginService
Authenticates a user and generates an API token.

```php
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;

class YourService
{
    public function __construct(
        private readonly LoginServiceInterface $login_service
    ) {}

    public function authenticate(LoginRequestDTO $dto): array
    {
        return $this->login_service->execute($dto);
    }
}
```

**Input DTO:**
```php
$dto = new LoginRequestDTO(
    email: 'user@example.com',
    password: 'password123',
    remember: false,
    client: 'admin'
);
```

**Response:**
```php
[
    'status_code' => 200,
    'success' => true,
    'message' => 'User logged in successfully',
    'data' => [
        'user' => [...],  // UserResponseDTO
        'token' => 'xxx...'
    ]
]
```

#### LogoutService
Revokes the user's API token and cleans up the session.

**Response:**
```php
[
    'status_code' => 200,
    'success' => true,
    'message' => 'User logged out successfully'
]
```

#### RefreshTokenService
Refreshes an expired or expiring authentication token.

**Response:**
```php
[
    'status_code' => 200,
    'success' => true,
    'message' => 'Token refreshed successfully',
    'data' => [
        'token' => 'new_token...'
    ]
]
```

### User Management Services

#### GetUserService
Retrieves one or multiple users with optional pagination, sorting, and eager loading.

```php
$dto = new GetUserDTO(
    user_id: 1,           // Optional: get specific user by ID
    user_uuid: 'xxx',     // Optional: get specific user by UUID
    sort_by: 'created_at',
    sort_type: 'DESC',
    with: ['roles', 'permissions'],  // Relations to load
    page: 1,
    per_page: 15,
    with_pagination: true
);

$result = $this->get_user_service->execute($dto);
```

#### StoreUserService
Creates a new user in the system.

```php
$dto = new StoreUserDTO(
    name: 'John Doe',
    email: 'john@example.com',
    password: 'securepassword123',
    is_active: true
);

$result = $this->store_user_service->execute($dto);
```

#### UpdateUserService
Updates an existing user's information.

```php
$dto = new UpdateUserDTO(
    user_id: 1,
    name: 'Jane Doe',
    email: 'jane@example.com',
    password: 'newpassword123'  // Optional
);

$result = $this->update_user_service->execute($dto);
```

#### DeleteUserService
Soft deletes a user (preserves data for audit purposes).

```php
$dto = new DeleteUserDTO(
    user_id: 1
);

$result = $this->delete_user_service->execute($dto);
```

## 🔗 API Endpoints

### Authentication Endpoints

All routes are prefixed with `/api/v0/admin/auth`

#### Login
**POST** `/api/v0/admin/auth/login`

```json
{
    "email": "admin@example.com",
    "password": "password123",
    "client": "admin"
}
```

Response:
```json
{
    "success": true,
    "message": "User logged in successfully",
    "data": {
        "user": {
            "id": 1,
            "uuid": "xxx-xxx-xxx-xxx",
            "name": "Admin User",
            "email": "admin@example.com",
            "is_active": true,
            "created_at": "2025-12-10T10:00:00.000000Z"
        },
        "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
    }
}
```

#### Logout
**POST** `/api/v0/admin/auth/logout`

Headers:
```
Authorization: Bearer {token}
```

#### Refresh Token
**POST** `/api/v0/admin/auth/refresh`

Headers:
```
Authorization: Bearer {token}
```

## 📝 Data Transfer Objects (DTOs)

### LoginRequestDTO
```php
class LoginRequestDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly bool $remember = false,
        public readonly ?string $client = null,
    ) {}
}
```

### AuthResponseDTO
```php
class AuthResponseDTO
{
    public function __construct(
        public readonly UserResponseDTO $user,
        public readonly string $token,
    ) {}
}
```

### UserResponseDTO
```php
class UserResponseDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $uuid,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $is_active,
        public readonly ?string $created_at,
    ) {}
    
    public static function fromModel(User $user): self { ... }
    public static function fromCollection($users): array { ... }
}
```

## 🛡️ Security Features

### Password Security
- Passwords are hashed using Laravel's bcrypt algorithm
- Password validation occurs at the service layer
- Passwords are never logged or exposed in responses

### Token Management
- API tokens generated via Laravel Passport
- Tokens are tied to specific client types (Admin, Web, Mobile)
- Token expiration is configurable
- Soft-deleted users cannot generate new tokens

### Data Protection
- User soft deletes preserve data for auditing
- Sensitive fields are hidden in responses
- UUID support for public-facing identifiers

## 🧪 Testing

The module includes comprehensive unit tests covering:

### Test Structure
```
Tests/Unit/
├── Services/
│   ├── Auth/
│   │   ├── LoginServiceTest.php
│   │   ├── LogoutServiceTest.php
│   │   └── RefreshTokenServiceTest.php
│   └── User/
│       ├── GetUserServiceTest.php
│       ├── StoreUserServiceTest.php
│       ├── UpdateUserServiceTest.php
│       └── DeleteUserServiceTest.php
```

### Running Tests

```bash
# Run all Auth tests
php artisan test modules/Auth/Tests

# Run specific test
php artisan test modules/Auth/Tests/Unit/Services/Auth/LoginServiceTest

# Run with coverage
php artisan test modules/Auth/Tests --coverage

# Run specific test method
php artisan test modules/Auth/Tests/Unit/Services/Auth/LoginServiceTest --filter testCanLoginSuccessfully
```

## 🔄 Service Provider

The `AuthModuleServiceProvider` handles:

1. **Service Registration** - Binds all service interfaces to implementations
2. **Route Loading** - Loads all authentication routes
3. **Migration Loading** - Registers database migrations
4. **Translation Loading** - Makes translation files available
5. **Command Registration** - Registers any module-specific Artisan commands

## 🔐 User Model

The `User` model extends Laravel's `Authenticatable` and implements `OAuthenticatable` for Passport support.

**Key Features:**
- Soft deletes for data preservation
- UUID support for public identifiers
- API token management via Passport
- Notification support
- Modular factory support

**Table:** `auth_users`

**Key Fields:**
```php
'id' => int,
'uuid' => uuid,
'name' => string,
'email' => string,
'password' => string (hashed),
'is_active' => boolean,
'deleted_at' => timestamp (soft delete),
'created_at' => timestamp,
'updated_at' => timestamp,
```

## 🔌 Integration with Other Modules

Other modules can depend on Auth services:

```php
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;
use Modules\Auth\Services\User\Contracts\GetUserServiceInterface;

class OrderService
{
    public function __construct(
        private readonly GetUserServiceInterface $get_user_service,
    ) {}

    public function processOrderForUser(int $user_id)
    {
        $dto = new GetUserDTO(user_id: $user_id);
        $user = $this->get_user_service->execute($dto);
        // Use user data for order processing
    }
}
```

## 📚 Database

### Migrations

The module includes migrations for:
- Users table creation
- API token management (Passport)

### Factories

**UserFactory** - Creates test users with realistic data:

```php
use Modules\Auth\Database\Factories\UserFactory;

$user = UserFactory::new()->create([
    'email' => 'test@example.com',
    'is_active' => true,
]);
```

## 🌐 Localization

Translation files are located in `Resources/lang/en/`:

- `auth.php` - Authentication messages
- `validation.php` - Validation error messages
- `errors.php` - Error messages

Access translations:
```php
trans('auth::auth.login.success')
trans('auth::auth.login.invalid_credentials')
```

## 🎯 Best Practices

### When Using Auth Services

1. **Always Use DTOs** - Pass data through DTOs for type safety
2. **Handle Exceptions** - Services may throw exceptions with specific codes
3. **Check Success Flag** - Always verify the `success` flag in responses
4. **Validate Input** - DTOs include validation logic
5. **Use Contracts** - Depend on interfaces, not concrete implementations

### Code Example

```php
use Modules\Auth\DTOs\LoginRequestDTO;
use Modules\Auth\Services\Auth\Contracts\LoginServiceInterface;

class AuthenticationController
{
    public function __construct(
        private readonly LoginServiceInterface $login_service,
    ) {}

    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $dto = LoginRequestDTO::fromArray($request->validated());
            $response = $this->login_service->execute($dto);
            
            return response()->json($response, $response['status_code']);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 500);
        }
    }
}
```

## 🤝 Contributing

When extending the Auth module:

1. **Create new services** by extending `BaseService` and implementing contracts
2. **Create DTOs** for all service inputs and outputs
3. **Add comprehensive tests** for new functionality
4. **Update this README** with new services and features
5. **Follow SOA principles** - Keep services focused and reusable

## 📋 Related Documentation

- [Make Module Command](../../../docs/commands/MAKE_MODULE.md) - How to create new modules
- [Contributing Guide](../../../CONTRIBUTING.md) - Contribution guidelines

## 📦 Dependencies

- **Laravel Framework** 12.x+
- **Laravel Passport** - OAuth 2.0 server implementation
- **PHPUnit** - Testing framework
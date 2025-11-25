# Auth Module

This module handles user authentication for the Laravel SOA Starter application.

## Features

- User Registration
- User Login/Logout
- User Profile Retrieval
- Session Management
- Data Transfer Objects (DTOs) for type safety
- Comprehensive test coverage

## API Endpoints

### Authentication Routes

All routes are prefixed with `/api/v1/auth`

#### Public Routes

- `POST /register` - Register a new user
- `POST /login` - Login user

#### Protected Routes (require authentication)

- `POST /logout` - Logout user
- `GET /user` - Get authenticated user profile
- `POST /refresh` - Refresh authentication session

## Request/Response Examples

### Register User

**POST** `/api/v1/auth/register`

Request:
```json
{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

Response (201):
```json
{
    "success": true,
    "message": "User registered successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2025-11-25T10:00:00.000000Z",
            "updated_at": "2025-11-25T10:00:00.000000Z"
        }
    }
}
```

### Login User

**POST** `/api/v1/auth/login`

Request:
```json
{
    "email": "john@example.com",
    "password": "password123",
    "remember": false
}
```

Response (200):
```json
{
    "success": true,
    "message": "User logged in successfully",
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "email_verified_at": null,
            "created_at": "2025-11-25T10:00:00.000000Z",
            "updated_at": "2025-11-25T10:00:00.000000Z"
        }
    }
}
```

### Get User Profile

**GET** `/api/v1/auth/user`

Headers:
```
Authorization: Bearer {session_cookie}
```

Response (200):
```json
{
    "success": true,
    "message": "User retrieved successfully",
    "data": {
        "id": 1,
        "name": "John Doe",
        "email": "john@example.com",
        "email_verified_at": null,
        "created_at": "2025-11-25T10:00:00.000000Z",
        "updated_at": "2025-11-25T10:00:00.000000Z"
    }
}
```

## Architecture

### DTOs (Data Transfer Objects)

- `LoginRequestDTO` - For login requests
- `RegisterRequestDTO` - For registration requests
- `UserResponseDTO` - For user data responses
- `AuthResponseDTO` - For authentication responses

### Services

- `AuthServiceInterface` - Contract defining authentication operations
- `AuthService` - Implementation of authentication business logic

### Controllers

- `AuthController` - HTTP layer handling authentication requests

## Testing

The module includes comprehensive tests:

### Feature Tests
- User registration flow
- User login flow
- Protected route access
- Authentication validation

### Unit Tests
- DTO creation and transformation
- Service layer logic
- Edge cases and error handling

Run tests with:
```bash
php artisan test --filter=Auth
```

## Usage in Other Modules

Other modules can depend on the Auth module by injecting the `AuthServiceInterface`:

```php
use Modules\Auth\Services\Contracts\AuthServiceInterface;

class SomeController extends Controller
{
    public function __construct(
        private readonly AuthServiceInterface $authService
    ) {}

    public function someMethod()
    {
        $user = $this->authService->user();
        // Use user data...
    }
}
```

## Extension Points

The Auth module is designed to be easily extensible:

1. **Add new authentication methods** - Implement additional login strategies
2. **Add user roles/permissions** - Extend the User model with authorization
3. **Add social authentication** - Integrate OAuth providers
4. **Add API tokens** - Implement API token authentication

## Dependencies

- Laravel Framework
- Laravel Authentication
- PHPUnit (for testing)
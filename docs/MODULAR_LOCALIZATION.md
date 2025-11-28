# Modular Localization

This document explains how localization is implemented in the modular architecture of this Laravel SOA Starter.

## Overview

Each module contains its own language files, allowing for better organization and maintainability of translations. The localization system follows Laravel's standard conventions while being organized within the modular structure.

## Directory Structure

```
modules/
├── Auth/
│   └── Resources/
│       └── lang/
│           └── en/
│               ├── auth.php      # Authentication messages
│               └── user.php      # User management messages
├── Product/
│   └── Resources/
│       └── lang/
│           └── en/
│               └── product.php   # Product-specific messages
└── Order/
    └── Resources/
        └── lang/
            └── en/
                └── order.php     # Order-specific messages
```

## Module Service Provider Setup

Each module's service provider automatically loads its translations using the `loadTranslationsFrom` method:

```php
/**
 * Load module translations.
 */
private function loadTranslations(): void
{
    $translationPath = __DIR__ . '/../Resources/lang';
    if (is_dir($translationPath)) {
        $this->loadTranslationsFrom($translationPath, 'auth'); // namespace matches module
    }
}
```

## Usage in Code

### Accessing Module Translations

Use the module namespace when accessing translations:

```php
// Auth module translations
__('auth::auth.login.success')
__('auth::auth.login.failed')
__('auth::user.profile.updated')

// Product module translations  
__('product::product.operations.created')
__('product::product.validation.name_required')

// Order module translations
__('order::order.operations.created')
__('order::order.validation.total_required')
```

### In Controllers

```php
class AuthController extends Controller
{
    public function login(LoginRequest $request)
    {
        // ... authentication logic
        
        return response()->json([
            'message' => __('auth::auth.login.success'),
            'user' => $user
        ]);
    }
}
```

### In Services

```php
class LoginService
{
    public function login(LoginDTO $login_dto): array
    {
        if (!$this->validateCredentials($login_dto)) {
            throw new ValidationException(__('auth::auth.login.invalid_credentials'));
        }
        
        // ... login logic
        
        return [
            'message' => __('auth::auth.login.success'),
            'token' => $token
        ];
    }
}
```

## Translation File Structure

Each module's translation files follow a consistent structure with snake_case keys:

```php
<?php

return [
    'operations' => [
        'created' => 'Item created successfully.',
        'updated' => 'Item updated successfully.',
        'deleted' => 'Item deleted successfully.',
        'retrieved' => 'Item retrieved successfully.',
        'not_found' => 'Item not found.',
        'create_failed' => 'Failed to create item.',
        'update_failed' => 'Failed to update item.',
        'delete_failed' => 'Failed to delete item.',
    ],

    'validation' => [
        'name_required' => 'Name is required.',
        'name_max' => 'Name may not be greater than 255 characters.',
        'invalid_data' => 'The provided data is invalid.',
    ],

    'errors' => [
        'service_unavailable' => 'Service is temporarily unavailable.',
        'unauthorized' => 'You are not authorized to perform this action.',
        'forbidden' => 'Access forbidden.',
    ],
];
```

## Generating New Modules

When creating new modules using the `make:module` command, translation files are automatically generated:

```bash
php artisan make:module Product
```

This creates:
- `modules/Product/Resources/lang/en/product.php` with basic translations
- Updates the service provider to load translations
- Sets up the proper namespace for the module

## Multi-Language Support

To add support for additional languages:

1. Create language directories in each module:
```
modules/Auth/Resources/lang/
├── en/
│   ├── auth.php
│   └── user.php
├── es/
│   ├── auth.php
│   └── user.php
└── fr/
    ├── auth.php
    └── user.php
```

2. The service provider automatically loads all available languages.

3. Use Laravel's standard locale switching:
```php
App::setLocale('es');
```

## Benefits

- **Modular Organization**: Each module's translations are self-contained
- **Namespace Isolation**: No translation key conflicts between modules
- **Maintainability**: Easy to find and update module-specific translations
- **Scalability**: New modules automatically get their own translation namespace
- **Consistency**: All modules follow the same translation structure
- **Team Development**: Different teams can work on different modules without conflicts

## Best Practices

1. **Use Descriptive Keys**: Use clear, hierarchical keys like `auth.login.success`
2. **Consistent Structure**: Follow the operations/validation/errors pattern
3. **Snake Case**: Use snake_case for all translation keys
4. **Module Namespacing**: Always use the module namespace when accessing translations
5. **Fallback Messages**: Provide meaningful default messages for all scenarios
6. **Plural Forms**: Use Laravel's pluralization features when needed

## Example Auth Module Implementation

The Auth module demonstrates the complete implementation:

**File**: `modules/Auth/Resources/lang/en/auth.php`
```php
return [
    'login' => [
        'success' => 'User logged in successfully.',
        'failed' => 'The provided credentials are incorrect.',
        'invalid_credentials' => 'Invalid credentials provided.',
    ],
    // ... more translations
];
```

**Usage in Service**:
```php
throw new ValidationException(__('auth::auth.login.invalid_credentials'));
```

This modular approach ensures that your application's translations remain organized and maintainable as it grows.
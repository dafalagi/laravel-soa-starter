# Make Resource Command

The `make:resource` command generates a new JSON resource class in a specific module with the same structured approach as controllers and requests, organizing resources by client type and feature.

## Command Signature

```bash
php artisan make:resource {module} {feature} {resource} {client}
```

## Arguments

| Argument | Description | Example |
|----------|-------------|---------|
| `module` | The module name where the resource will be created | `Auth`, `Product`, `Order` |
| `feature` | The feature name for organizing resources | `Authentication`, `User`, `Profile` |
| `resource` | The resource name | `UserList`, `UserDetail`, `ProductCard` |
| `client` | The client type (Web/Admin/Mobile) | `Web`, `Admin`, `Mobile` |

## Generated Structure

JSON resources are organized in a hierarchical structure within each module, mirroring the request and controller structure:

```
modules/
└── {Module}/
    └── Http/
        └── Resources/
            └── Api/
                ├── Web/
                │   └── {Feature}/
                │       └── {Resource}Resource.php
                ├── Admin/
                │   └── {Feature}/
                │       └── {Resource}Resource.php
                └── Mobile/
                    └── {Feature}/
                        └── {Resource}Resource.php
```

## Usage Examples

### User Management Resources

```bash
# Admin user resources
php artisan make:resource Auth User UserList Admin
php artisan make:resource Auth User UserDetail Admin

# Web user resources  
php artisan make:resource Auth User UserProfile Web

# Mobile user resources
php artisan make:resource Auth User UserCard Mobile
```

**Generated paths:**
- `modules/Auth/Http/Resources/Api/Admin/User/UserListResource.php`
- `modules/Auth/Http/Resources/Api/Admin/User/UserDetailResource.php`
- `modules/Auth/Http/Resources/Api/Web/User/UserProfileResource.php`
- `modules/Auth/Http/Resources/Api/Mobile/User/UserCardResource.php`

### Product Management Resources

```bash
# Admin product resources
php artisan make:resource Product Catalog ProductList Admin
php artisan make:resource Product Catalog ProductDetail Admin

# Web product browsing resources
php artisan make:resource Product Catalog ProductCard Web
php artisan make:resource Product Search ProductSearch Web

# Mobile product resources
php artisan make:resource Product Catalog ProductMobile Mobile
```

**Generated paths:**
- `modules/Product/Http/Resources/Api/Admin/Catalog/ProductListResource.php`
- `modules/Product/Http/Resources/Api/Admin/Catalog/ProductDetailResource.php`
- `modules/Product/Http/Resources/Api/Web/Catalog/ProductCardResource.php`
- `modules/Product/Http/Resources/Api/Web/Search/ProductSearchResource.php`
- `modules/Product/Http/Resources/Api/Mobile/Catalog/ProductMobileResource.php`

## Generated Resource Structure

Each generated JSON resource includes:

### Basic Structure
```php
<?php

namespace Modules\{Module}\Http\Resources\Api\{Client}\{Feature};

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class {Resource}Resource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
```

### Key Features

- **JSON Resource Base**: Extends `JsonResource` for API response formatting
- **Basic Fields**: Includes common fields (uuid, timestamps) as starting point
- **Type Hints**: Proper return type annotation for IDE support
- **Request Context**: Access to current request for conditional field inclusion
- **Clean Structure**: Minimal boilerplate, ready for customization

## Customization Examples

### Adding More Fields
```php
public function toArray(Request $request): array
{
    return [
        'uuid' => $this->uuid,
        'name' => $this->name,
        'email' => $this->email,
        'status' => $this->status,
        'profile' => [
            'avatar' => $this->avatar,
            'bio' => $this->bio,
        ],
        'created_at' => $this->created_at,
        'updated_at' => $this->updated_at,
    ];
}
```

### Conditional Fields
```php
public function toArray(Request $request): array
{
    return [
        'uuid' => $this->uuid,
        'name' => $this->name,
        'email' => $this->when($request->user()->isAdmin(), $this->email),
        'sensitive_data' => $this->when(
            $request->user()->can('view-sensitive-data'), 
            $this->sensitive_data
        ),
        'created_at' => $this->created_at,
    ];
}
```

### Including Relationships
```php
public function toArray(Request $request): array
{
    return [
        'uuid' => $this->uuid,
        'name' => $this->name,
        'posts' => PostResource::collection($this->whenLoaded('posts')),
        'profile' => new ProfileResource($this->whenLoaded('profile')),
        'created_at' => $this->created_at,
    ];
}
```

## Integration with Controllers

Generated resources can be easily integrated with controllers:

```php
// In your controller
use Modules\Auth\Http\Resources\Api\Admin\User\UserListResource;
use Modules\Auth\Http\Resources\Api\Admin\User\UserDetailResource;

class UserController extends ApiController
{
    public function index(): JsonResponse
    {
        $users = User::paginate();
        
        return $this->response([
            'users' => UserListResource::collection($users)
        ]);
    }
    
    public function show(User $user): JsonResponse
    {
        return $this->response([
            'user' => new UserDetailResource($user)
        ]);
    }
}
```

## Resource Collections

For handling collections of resources:

```php
// Create a resource collection
use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'total_count' => $this->collection->count(),
                'has_more' => $this->hasPages(),
            ],
        ];
    }
}
```

## Best Practices

### Naming Conventions
- **Module**: PascalCase (`Auth`, `ProductManagement`)
- **Feature**: PascalCase (`Authentication`, `UserProfile`)
- **Resource**: PascalCase describing the data representation (`UserList`, `ProductCard`)
- **Client**: PascalCase (`Web`, `Admin`, `Mobile`)

### Resource Design Strategy
- **List Resources**: For index/collection endpoints (minimal data)
- **Detail Resources**: For show endpoints (comprehensive data)
- **Card Resources**: For summary/preview displays
- **Mobile Resources**: Optimized for mobile bandwidth

### Organization Examples
```
Auth/Http/Resources/Api/
├── Admin/
│   └── User/
│       ├── UserListResource.php      # For user listings
│       ├── UserDetailResource.php    # For single user view
│       └── UserEditResource.php      # For edit forms
├── Web/
│   └── User/
│       ├── UserProfileResource.php   # Public profile
│       └── UserCardResource.php      # User cards/avatars
└── Mobile/
    └── User/
        └── UserMobileResource.php    # Optimized for mobile
```

### Field Selection Strategy
- **Admin Resources**: Include all necessary fields for management
- **Web Resources**: Include public-safe fields
- **Mobile Resources**: Minimize data for performance
- **Use Conditional Fields**: Based on permissions and context

## Validation & Error Handling

The command performs several validations:

1. **Module Exists**: Verifies the specified module directory exists
2. **Resource Naming**: Automatically appends "Resource" if not present
3. **Client Validation**: Ensures client is one of: Web, Admin, Mobile
4. **Duplicate Check**: Prevents overwriting existing resources

## Directory Creation

The command automatically creates the directory structure if it doesn't exist:
- Creates feature directories (`{Module}/Http/Resources/Api/{Client}/{Feature}/`)
- Maintains consistent folder structure across all modules

## Error Messages

The command provides clear error messages for common issues:

- **Module Not Found**: "Module {ModuleName} does not exist!"
- **Invalid Client**: "Client must be one of: Web, Admin, Mobile"
- **Resource Exists**: "Resource {ResourceName} already exists in {ModuleName} module!"

## Success Output

Upon successful creation, the command outputs:
```
Resource {ResourceName} created successfully in {ModuleName} module!
```

## Advanced Usage

### Custom Resource Methods
```php
class UserDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'uuid' => $this->uuid,
            'name' => $this->name,
            'full_name' => $this->getFullName(),
            'avatar_url' => $this->getAvatarUrl(),
            'is_online' => $this->isOnline(),
            'created_at' => $this->created_at,
        ];
    }
    
    private function getFullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
    
    private function getAvatarUrl(): string
    {
        return $this->avatar 
            ? Storage::url($this->avatar)
            : asset('images/default-avatar.png');
    }
    
    private function isOnline(): bool
    {
        return $this->last_activity_at > now()->subMinutes(5);
    }
}
```

This organized approach ensures that JSON resources are properly structured, maintain consistency with the overall architecture, and provide flexible data transformation for different client needs and use cases.
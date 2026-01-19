# Permission System Usage Guide

This document explains how to use the flexible permission system built with Spatie Roles & Permissions.

## Overview

The permission system supports:
- **Multiple roles per user**: Users can have multiple roles assigned
- **Direct permissions**: Permissions can be assigned directly to users
- **Automatic deduplication**: Permissions from multiple roles are automatically deduplicated
- **Centralized checking**: Single source of truth for permission validation

## Architecture

### Components

1. **PermissionService** (`app/Services/PermissionService.php`)
   - Handles role and permission assignment
   - Deduplicates permissions from multiple roles
   - Provides permission checking methods

2. **CheckPermissionsTrait** (`app/Traits/CheckPermissionsTrait.php`)
   - Provides easy-to-use methods for controllers and services
   - Centralized permission checking

3. **CheckPermission Middleware** (`app/Http/Middleware/CheckPermission.php`)
   - Route-level permission protection

4. **User Model Helpers**
   - Convenience methods for permission checking

## Usage Examples

### 1. Assigning Roles and Permissions

#### Via PermissionService

```php
use App\Models\User;
use App\Services\PermissionService;

$user = User::find(1);
$permissionService = app(PermissionService::class);

// Assign roles (by ID, name, or Role model)
$permissionService->assignRolesAndPermissions(
    $user,
    roles: [1, 2, 'admin'], // Can mix IDs and names
    directPermissions: ['products-add', 'products-edit'] // Optional direct permissions
);
```

#### Via UserService

```php
use App\Models\User;
use App\Services\UserService;

$user = User::find(1);
$userService = app(UserService::class);

// Assign roles and permissions
$userService->assignRolesAndPermissions(
    $user,
    roles: ['admin', 'manager'],
    directPermissions: ['special-permission']
);
```

### 2. Permission Checking in Controllers

#### Using CheckPermissionsTrait

```php
use App\Http\Controllers\Controller;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    use CheckPermissionsTrait;

    /**
     * List products - requires 'products-index' permission
     */
    public function index(): JsonResponse
    {
        // Check permission before executing business logic
        $this->requirePermission('products-index');

        // Your business logic here
        // ...

        return response()->json(['data' => $products]);
    }

    /**
     * Create product - requires any of the listed permissions
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user has any of these permissions
        $this->requireAnyPermission([
            'products-add',
            'products-manage'
        ]);

        // Your business logic here
        // ...
    }

    /**
     * Update product - requires all listed permissions
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Check if user has all of these permissions
        $this->requireAllPermissions([
            'products-edit',
            'products-update-stock'
        ]);

        // Your business logic here
        // ...
    }

    /**
     * Conditional permission check
     */
    public function delete($id): JsonResponse
    {
        // Check permission without throwing exception
        if (!$this->userHasPermission('products-delete')) {
            return response()->json([
                'message' => 'Insufficient permissions'
            ], 403);
        }

        // Your business logic here
        // ...
    }
}
```

### 3. Permission Checking in Services

```php
use App\Services\BaseService;
use App\Traits\CheckPermissionsTrait;
use App\Models\User;

class ProductService extends BaseService
{
    use CheckPermissionsTrait;

    public function createProduct(array $data, User $user): array
    {
        // Ensure user has permission before proceeding
        $this->requirePermission('products-add');

        // Your business logic here
        // ...
    }

    public function updateProduct(int $id, array $data, User $user): array
    {
        // Check permission with custom message
        $this->requirePermission(
            'products-edit',
            message: 'You cannot edit products. Please contact an administrator.',
            statusCode: 403
        );

        // Your business logic here
        // ...
    }
}
```

### 4. Route-Level Protection (Middleware)

#### In routes/api.php

```php
use Illuminate\Support\Facades\Route;

// Single permission required
Route::middleware(['auth:sanctum', 'permission:products-index'])->get('/products', [ProductController::class, 'index']);

// Multiple permissions (user needs any one of them)
Route::middleware(['auth:sanctum', 'permission:products-add|products-manage'])->post('/products', [ProductController::class, 'store']);

// Separate middleware calls for each permission (user needs all)
Route::middleware(['auth:sanctum', 'permission:products-edit', 'permission:products-update-stock'])->put('/products/{id}', [ProductController::class, 'update']);
```

### 5. User Model Helpers

```php
$user = User::find(1);

// Check single permission
if ($user->canPerform('products-add')) {
    // User has permission
}

// Check any permission
if ($user->canPerformAny(['products-add', 'products-edit'])) {
    // User has at least one permission
}

// Check all permissions
if ($user->canPerformAll(['products-add', 'products-edit'])) {
    // User has all permissions
}

// Get all user permissions (from roles + direct)
$permissions = $user->getAllUserPermissions();
```

### 6. Syncing Permissions After Role Changes

When roles are updated, automatically sync permissions:

```php
use App\Services\PermissionService;

$permissionService = app(PermissionService::class);

// Assign new roles
$permissionService->assignRoles($user, ['admin', 'manager']);

// This automatically syncs permissions from roles
// Or manually sync:
$permissionService->syncUserPermissions($user);
```

### 7. Role Checking

```php
use App\Services\PermissionService;

$permissionService = app(PermissionService::class);

// Check single role
if ($permissionService->hasRole($user, 'admin')) {
    // User has admin role
}

// Check any role
if ($permissionService->hasAnyRole($user, ['admin', 'manager'])) {
    // User has at least one role
}

// Using trait in controller
$this->requireRole('admin'); // Throws exception if user doesn't have role
$this->requireAnyRole(['admin', 'manager']); // Throws exception if user doesn't have any role
```

## Best Practices

### 1. Check Permissions Early

Always check permissions at the beginning of methods, before any business logic:

```php
public function deleteProduct(int $id): JsonResponse
{
    // ✅ Good: Check permission first
    $this->requirePermission('products-delete');

    // Business logic here
    $product = Product::findOrFail($id);
    $product->delete();

    return response()->json(['message' => 'Product deleted']);
}
```

### 2. Use Services for Permission Logic

Avoid checking permissions directly in controllers when possible. Use services:

```php
// ✅ Good: Permission check in service
class ProductService
{
    use CheckPermissionsTrait;

    public function delete(int $id): void
    {
        $this->requirePermission('products-delete');
        // Business logic
    }
}

// Controller just calls service
class ProductController
{
    public function destroy(int $id)
    {
        $this->productService->delete($id);
        return response()->json(['message' => 'Deleted']);
    }
}
```

### 3. Use Middleware for Route Protection

Use middleware for route-level protection when possible:

```php
// ✅ Good: Middleware handles permission check
Route::middleware(['auth:sanctum', 'permission:products-delete'])
    ->delete('/products/{id}', [ProductController::class, 'destroy']);

// Controller method can focus on business logic
public function destroy(int $id)
{
    // Permission already checked by middleware
    Product::findOrFail($id)->delete();
    return response()->json(['message' => 'Deleted']);
}
```

### 4. Centralize Permission Names

Consider creating a constants class for permission names:

```php
// app/Constants/Permissions.php
class Permissions
{
    public const PRODUCTS_INDEX = 'products-index';
    public const PRODUCTS_ADD = 'products-add';
    public const PRODUCTS_EDIT = 'products-edit';
    public const PRODUCTS_DELETE = 'products-delete';
}

// Usage
$this->requirePermission(Permissions::PRODUCTS_ADD);
```

## Common Patterns

### Pattern 1: CRUD Operations

```php
class ProductController extends Controller
{
    use CheckPermissionsTrait;

    public function index()
    {
        $this->requirePermission('products-index');
        // List products
    }

    public function store(Request $request)
    {
        $this->requirePermission('products-add');
        // Create product
    }

    public function update(Request $request, $id)
    {
        $this->requirePermission('products-edit');
        // Update product
    }

    public function destroy($id)
    {
        $this->requirePermission('products-delete');
        // Delete product
    }
}
```

### Pattern 2: Conditional Access

```php
public function show($id)
{
    $product = Product::findOrFail($id);

    // Only show sensitive data if user has permission
    if ($this->userHasPermission('products-view-details')) {
        return response()->json([
            'id' => $product->id,
            'name' => $product->name,
            'cost' => $product->cost, // Sensitive data
            'profit_margin' => $product->profit_margin,
        ]);
    }

    // Basic data for users without permission
    return response()->json([
        'id' => $product->id,
        'name' => $product->name,
        'price' => $product->price,
    ]);
}
```

### Pattern 3: Service-Level Protection

```php
class OrderService extends BaseService
{
    use CheckPermissionsTrait;

    public function processOrder(array $data, User $user): array
    {
        // Check multiple permissions
        $this->requireAllPermissions([
            'orders-create',
            'inventory-manage'
        ]);

        // Business logic
        // ...
    }
}
```

## Migration from Old Implementation

If you have existing permission logic, migrate it gradually:

1. **Keep old checks temporarily**: Don't remove old code immediately
2. **Add new checks**: Add new permission checks using the new system
3. **Test thoroughly**: Ensure all permission checks work correctly
4. **Remove old code**: Once verified, remove old permission logic

## Troubleshooting

### Permission Not Working?

1. **Check if roles are assigned**: `$user->roles`
2. **Check if permissions are synced**: `$user->getAllPermissions()`
3. **Verify permission name**: Make sure the permission name matches exactly
4. **Check guard**: Ensure you're using the correct guard ('web' by default)

### Permissions Not Syncing?

After assigning roles, call `syncUserPermissions()`:

```php
$permissionService->assignRoles($user, ['admin']);
$permissionService->syncUserPermissions($user);
```

Or use `assignRolesAndPermissions()` which automatically syncs:

```php
$permissionService->assignRolesAndPermissions($user, roles: ['admin']);
```

## API Reference

### PermissionService Methods

- `assignRolesAndPermissions(User $user, ?array $roles, ?array $directPermissions)`: Assign roles and permissions with auto-deduplication
- `assignRoles(User $user, array $roles)`: Assign roles to user
- `syncUserPermissions(User $user, ?array $directPermissions)`: Sync permissions from roles + direct permissions
- `checkPermission(User $user, string $permission)`: Check if user has permission
- `hasAnyPermission(User $user, array $permissions)`: Check if user has any permission
- `hasAllPermissions(User $user, array $permissions)`: Check if user has all permissions
- `getUserPermissions(User $user)`: Get all user permissions
- `getUserRoles(User $user)`: Get all user roles

### CheckPermissionsTrait Methods

- `userHasPermission(string $permission, ?User $user)`: Check permission (non-throwing)
- `userHasAnyPermission(array $permissions, ?User $user)`: Check any permission (non-throwing)
- `userHasAllPermissions(array $permissions, ?User $user)`: Check all permissions (non-throwing)
- `requirePermission(string $permission, ?string $message, int $statusCode)`: Require permission (throws exception)
- `requireAnyPermission(array $permissions, ?string $message, int $statusCode)`: Require any permission (throws exception)
- `requireAllPermissions(array $permissions, ?string $message, int $statusCode)`: Require all permissions (throws exception)
- `userHasRole(string $role, ?User $user)`: Check role (non-throwing)
- `requireRole(string $role, ?string $message, int $statusCode)`: Require role (throws exception)

### User Model Methods

- `canPerform(string $permission)`: Check permission
- `canPerformAny(array $permissions)`: Check any permission
- `canPerformAll(array $permissions)`: Check all permissions
- `getAllUserPermissions()`: Get all permissions

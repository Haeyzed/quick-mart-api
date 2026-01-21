Ov23liasAt6sx2fl8tDi

4cb8fe215dc1c611697235e0be6aa58e91ee44f8

http://localhost:3000/auth/callback/github

# Frontend Permission Integration Guide

This document provides a comprehensive guide for integrating the permission system with the Next.js frontend application using NextAuth.

## Overview

The permission system is fully integrated on the backend and exposes user roles and permissions through the API. The frontend should consume this data and enforce permissions for UI/UX control, while the backend remains the source of truth for authorization.

## API Response Structure

When a user logs in or fetches their profile, the API returns the following structure:

```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "roles": [
        {
          "id": 1,
          "name": "Admin",
          "guard_name": "web"
        }
      ],
      "permissions": [
        {
          "id": 4,
          "name": "products-edit",
          "guard_name": "web"
        }
      ],
      "all_permissions": [
        "products-index",
        "products-add",
        "products-edit",
        "products-delete",
        "category",
        "brand",
        "unit",
        "tax",
        "users-index",
        "users-add",
        "users-edit",
        "users-delete",
        "product_history"
      ],
      "role_names": ["Admin"]
    },
    "token": "sanctum-token-here"
  }
}
```

## Permission Naming Convention

The system uses a consistent naming convention:

### Products
- `products-index` - View products list
- `products-add` - Create products
- `products-edit` - Update products
- `products-delete` - Delete products
- `product_history` - View product history

### Categories
- `category` - All category operations (index, add, edit, delete, import, export)

### Brands
- `brand` - All brand operations (index, add, edit, delete, import, export)

### Units
- `unit` - All unit operations (index, add, edit, delete, import, export)

### Taxes
- `tax` - All tax operations (index, add, edit, delete, import, export)

### Users
- `users-index` - View users list
- `users-add` - Create users
- `users-edit` - Update users
- `users-delete` - Delete users

## NextAuth Integration

### 1. Update NextAuth Configuration

Update your NextAuth configuration to include permissions in the session and JWT:

```typescript
// app/api/auth/[...nextauth]/route.ts or pages/api/auth/[...nextauth].ts

import NextAuth from "next-auth";
import CredentialsProvider from "next-auth/providers/credentials";

export const authOptions = {
  providers: [
    CredentialsProvider({
      name: "Credentials",
      credentials: {
        name: { label: "Username/Email", type: "text" },
        password: { label: "Password", type: "password" },
      },
      async authorize(credentials) {
        const res = await fetch(`${process.env.NEXT_PUBLIC_API_URL}/api/login`, {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            name: credentials?.name,
            password: credentials?.password,
          }),
        });

        const data = await res.json();

        if (data.success && data.data.user) {
          const user = data.data.user;
          return {
            id: user.id.toString(),
            email: user.email,
            name: user.name,
            token: data.data.token,
            roles: user.roles || [],
            permissions: user.all_permissions || [],
            roleNames: user.role_names || [],
          };
        }

        return null;
      },
    }),
  ],
  callbacks: {
    async jwt({ token, user }) {
      if (user) {
        token.accessToken = user.token;
        token.roles = user.roles;
        token.permissions = user.permissions;
        token.roleNames = user.roleNames;
      }
      return token;
    },
    async session({ session, token }) {
      if (session.user) {
        session.user.id = token.sub;
        session.accessToken = token.accessToken;
        session.roles = token.roles;
        session.permissions = token.permissions;
        session.roleNames = token.roleNames;
      }
      return session;
    },
  },
  pages: {
    signIn: "/login",
  },
  session: {
    strategy: "jwt",
  },
};

export default NextAuth(authOptions);
```

### 2. Type Definitions

Create TypeScript types for the session:

```typescript
// types/next-auth.d.ts

import "next-auth";
import "next-auth/jwt";

declare module "next-auth" {
  interface Session {
    user: {
      id: string;
      email: string;
      name: string;
    };
    accessToken: string;
    roles: Array<{ id: number; name: string; guard_name: string }>;
    permissions: string[];
    roleNames: string[];
  }

  interface User {
    id: string;
    email: string;
    name: string;
    token: string;
    roles: Array<{ id: number; name: string; guard_name: string }>;
    permissions: string[];
    roleNames: string[];
  }
}

declare module "next-auth/jwt" {
  interface JWT {
    accessToken: string;
    roles: Array<{ id: number; name: string; guard_name: string }>;
    permissions: string[];
    roleNames: string[];
  }
}
```

### 3. Permission Utility Functions

Create utility functions for permission checks:

```typescript
// lib/permissions.ts

import { useSession } from "next-auth/react";

/**
 * Check if the current user has a specific permission.
 *
 * @param permission - The permission name to check
 * @returns boolean
 */
export function useHasPermission(permission: string): boolean {
  const { data: session } = useSession();
  if (!session?.permissions) return false;
  return session.permissions.includes(permission);
}

/**
 * Check if the current user has any of the given permissions.
 *
 * @param permissions - Array of permission names to check
 * @returns boolean
 */
export function useHasAnyPermission(permissions: string[]): boolean {
  const { data: session } = useSession();
  if (!session?.permissions) return false;
  return permissions.some((permission) => session.permissions.includes(permission));
}

/**
 * Check if the current user has all of the given permissions.
 *
 * @param permissions - Array of permission names to check
 * @returns boolean
 */
export function useHasAllPermissions(permissions: string[]): boolean {
  const { data: session } = useSession();
  if (!session?.permissions) return false;
  return permissions.every((permission) => session.permissions.includes(permission));
}

/**
 * Check if the current user has a specific role.
 *
 * @param roleName - The role name to check
 * @returns boolean
 */
export function useHasRole(roleName: string): boolean {
  const { data: session } = useSession();
  if (!session?.roleNames) return false;
  return session.roleNames.includes(roleName);
}

/**
 * Get all permissions for the current user.
 *
 * @returns string[]
 */
export function usePermissions(): string[] {
  const { data: session } = useSession();
  return session?.permissions || [];
}

/**
 * Get all roles for the current user.
 *
 * @returns string[]
 */
export function useRoles(): string[] {
  const { data: session } = useSession();
  return session?.roleNames || [];
}
```

### 4. Permission HOC/Component

Create a Higher-Order Component or component wrapper for permission-based rendering:

```typescript
// components/PermissionGuard.tsx

import { useSession } from "next-auth/react";
import { ReactNode } from "react";

interface PermissionGuardProps {
  permission?: string;
  permissions?: string[];
  requireAll?: boolean;
  role?: string;
  roles?: string[];
  fallback?: ReactNode;
  children: ReactNode;
}

export function PermissionGuard({
  permission,
  permissions,
  requireAll = false,
  role,
  roles,
  fallback = null,
  children,
}: PermissionGuardProps) {
  const { data: session } = useSession();

  // Check role-based access
  if (role || roles) {
    const roleNames = session?.roleNames || [];
    const rolesToCheck = roles || (role ? [role] : []);
    const hasRole = rolesToCheck.some((r) => roleNames.includes(r));
    if (!hasRole) return <>{fallback}</>;
  }

  // Check permission-based access
  if (permission || permissions) {
    const userPermissions = session?.permissions || [];
    const permissionsToCheck = permissions || (permission ? [permission] : []);

    const hasPermission = requireAll
      ? permissionsToCheck.every((p) => userPermissions.includes(p))
      : permissionsToCheck.some((p) => userPermissions.includes(p));

    if (!hasPermission) return <>{fallback}</>;
  }

  return <>{children}</>;
}
```

### 5. Usage Examples

#### Protecting Pages

```typescript
// app/products/page.tsx or pages/products/index.tsx

import { PermissionGuard } from "@/components/PermissionGuard";
import { useHasPermission } from "@/lib/permissions";

export default function ProductsPage() {
  const canView = useHasPermission("products-index");

  if (!canView) {
    return <div>You don't have permission to view products.</div>;
  }

  return (
    <div>
      <h1>Products</h1>
      <PermissionGuard permission="products-add">
        <button>Add Product</button>
      </PermissionGuard>
      {/* Product list */}
    </div>
  );
}
```

#### Protecting Action Buttons

```typescript
// components/ProductActions.tsx

import { PermissionGuard } from "@/components/PermissionGuard";

export function ProductActions({ productId }: { productId: number }) {
  return (
    <div className="flex gap-2">
      <PermissionGuard permission="products-edit">
        <button>Edit</button>
      </PermissionGuard>
      <PermissionGuard permission="products-delete">
        <button>Delete</button>
      </PermissionGuard>
      <PermissionGuard permission="product_history">
        <button>View History</button>
      </PermissionGuard>
    </div>
  );
}
```

#### Protecting Table Columns/Actions

```typescript
// components/ProductsTable.tsx

import { PermissionGuard } from "@/components/PermissionGuard";
import { useHasPermission } from "@/lib/permissions";

export function ProductsTable({ products }: { products: any[] }) {
  const canEdit = useHasPermission("products-edit");
  const canDelete = useHasPermission("products-delete");

  return (
    <table>
      <thead>
        <tr>
          <th>Name</th>
          <th>Code</th>
          <th>Price</th>
          {(canEdit || canDelete) && <th>Actions</th>}
        </tr>
      </thead>
      <tbody>
        {products.map((product) => (
          <tr key={product.id}>
            <td>{product.name}</td>
            <td>{product.code}</td>
            <td>{product.price}</td>
            {(canEdit || canDelete) && (
              <td>
                <PermissionGuard permission="products-edit">
                  <button>Edit</button>
                </PermissionGuard>
                <PermissionGuard permission="products-delete">
                  <button>Delete</button>
                </PermissionGuard>
              </td>
            )}
          </tr>
        ))}
      </tbody>
    </table>
  );
}
```

#### Protecting Routes (Middleware)

```typescript
// middleware.ts

import { withAuth } from "next-auth/middleware";
import { NextResponse } from "next/server";

export default withAuth(
  function middleware(req) {
    const token = req.nextauth.token;
    const path = req.nextUrl.pathname;

    // Define route-permission mappings
    const routePermissions: Record<string, string> = {
      "/products": "products-index",
      "/products/create": "products-add",
      "/categories": "category",
      "/brands": "brand",
      "/units": "unit",
      "/taxes": "tax",
      "/users": "users-index",
    };

    const requiredPermission = routePermissions[path];

    if (requiredPermission) {
      const userPermissions = (token?.permissions as string[]) || [];
      if (!userPermissions.includes(requiredPermission)) {
        return NextResponse.redirect(new URL("/unauthorized", req.url));
      }
    }

    return NextResponse.next();
  },
  {
    callbacks: {
      authorized: ({ token }) => !!token,
    },
  }
);

export const config = {
  matcher: [
    "/products/:path*",
    "/categories/:path*",
    "/brands/:path*",
    "/units/:path*",
    "/taxes/:path*",
    "/users/:path*",
  ],
};
```

## Best Practices

1. **Backend is Source of Truth**: Always remember that backend authorization is the source of truth. Frontend checks are only for UI/UX control.

2. **Consistent Permission Names**: Use the exact same permission names on both backend and frontend.

3. **Graceful Degradation**: If permissions are not loaded, hide protected features rather than showing errors.

4. **Refresh Permissions**: When user permissions are updated, refresh the session to get the latest permissions.

5. **Type Safety**: Use TypeScript to ensure type safety for permissions and roles.

## Permission Refresh

To refresh permissions after they've been updated:

```typescript
// lib/auth.ts

import { signIn } from "next-auth/react";

export async function refreshUserSession() {
  // Re-authenticate to get updated permissions
  await signIn("credentials", {
    redirect: false,
    // Use stored credentials or prompt user
  });
}
```

## Example: Complete Product Page with Permissions

```typescript
// app/products/page.tsx

"use client";

import { useSession } from "next-auth/react";
import { PermissionGuard } from "@/components/PermissionGuard";
import { useHasPermission } from "@/lib/permissions";
import { ProductsTable } from "@/components/ProductsTable";

export default function ProductsPage() {
  const { data: session, status } = useSession();
  const canView = useHasPermission("products-index");
  const canAdd = useHasPermission("products-add");
  const canImport = useHasPermission("products-add"); // Same as add

  if (status === "loading") {
    return <div>Loading...</div>;
  }

  if (!canView) {
    return (
      <div className="p-4">
        <h1>Access Denied</h1>
        <p>You don't have permission to view products.</p>
      </div>
    );
  }

  return (
    <div className="p-4">
      <div className="flex justify-between items-center mb-4">
        <h1 className="text-2xl font-bold">Products</h1>
        <div className="flex gap-2">
          <PermissionGuard permission="products-add">
            <button className="btn btn-primary">Add Product</button>
          </PermissionGuard>
          <PermissionGuard permission="products-add">
            <button className="btn btn-secondary">Import</button>
          </PermissionGuard>
        </div>
      </div>

      <ProductsTable />
    </div>
  );
}
```

## Testing Permissions

Create test utilities for permission testing:

```typescript
// __tests__/utils/permissions.ts

export const mockSessionWithPermissions = (permissions: string[]) => ({
  user: {
    id: "1",
    email: "test@example.com",
    name: "Test User",
  },
  accessToken: "mock-token",
  roles: [{ id: 1, name: "Admin", guard_name: "web" }],
  permissions,
  roleNames: ["Admin"],
});
```

This integration ensures that permissions are consistently enforced across both backend and frontend, providing a seamless and secure user experience.

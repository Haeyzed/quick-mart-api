<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * CheckPermission Middleware
 *
 * Middleware for route-level permission checking.
 * Use this middleware to protect routes that require specific permissions.
 *
 * Usage in routes:
 * Route::middleware(['auth:sanctum', 'permission:products-index'])->get('/products', ...);
 * Route::middleware(['auth:sanctum', 'permission:products-add|products-edit'])->post('/products', ...);
 */
class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string ...$permissions One or more permission names separated by pipe (|)
     * @return SymfonyResponse
     */
    public function handle(Request $request, Closure $next, string ...$permissions): SymfonyResponse
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthenticated.',
            ], Response::HTTP_UNAUTHORIZED);
        }

        // If multiple permissions are provided, user needs any one of them
        if (count($permissions) > 1) {
            if (!$user->hasAnyPermission($permissions)) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to perform this action.',
                    'required_permissions' => $permissions,
                ], Response::HTTP_FORBIDDEN);
            }
        } else {
            // Single permission check
            if (!$user->hasPermissionTo($permissions[0])) {
                return response()->json([
                    'status' => false,
                    'message' => 'You do not have permission to perform this action.',
                    'required_permission' => $permissions[0],
                ], Response::HTTP_FORBIDDEN);
            }
        }

        return $next($request);
    }
}

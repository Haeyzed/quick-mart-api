<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use Database\Seeders\Tenant\Support\PermissionModuleResolver;

/**
 * Tenant Database Seeder V2
 *
 * Refactored tenant seeder using extracted support classes for better maintainability.
 * Extends the original TenantDatabaseSeeder and overrides permission module resolution
 * to use the dedicated PermissionModuleResolver.
 *
 * Usage: Same as TenantDatabaseSeeder. Set TenantDatabaseSeeder::$tenantData and run.
 * To use this seeder, update your tenant creation flow to call TenantDatabaseSeederV2.
 */
class TenantDatabaseSeederV2 extends TenantDatabaseSeeder
{
    /**
     * Map permission name to module for grouping.
     */
    protected function getModuleForPermission(string $name): string
    {
        return PermissionModuleResolver::resolve($name);
    }
}

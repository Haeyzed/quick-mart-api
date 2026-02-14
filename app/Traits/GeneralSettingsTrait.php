<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\GeneralSetting;

/**
 * GeneralSettingsTrait
 *
 * Provides reusable methods for accessing general settings across services.
 * Ensures consistent and centralized access to application-wide settings.
 */
trait GeneralSettingsTrait
{
    /**
     * Check if a module is enabled.
     *
     * @param string $moduleName Module name to check (e.g., 'ecommerce', 'restaurant')
     * @return bool
     */
    protected function isModuleEnabled(string $moduleName): bool
    {
        $generalSetting = $this->getGeneralSettings();

        if (!$generalSetting || !$generalSetting->modules) {
            return false;
        }

        $modules = explode(',', $generalSetting->modules);

        return in_array($moduleName, $modules);
    }

    /**
     * Get the general settings instance (singleton pattern).
     *
     * @return GeneralSetting|null
     */
    protected function getGeneralSettings(): ?GeneralSetting
    {
        return GeneralSetting::latest()->first();
    }

    /**
     * Get the storage provider from general settings.
     *
     * @return string Storage provider name (default: 'public')
     */
    protected function getStorageProvider(): string
    {
        $generalSetting = $this->getGeneralSettings();

        return $generalSetting->storage_provider ?? 'public';
    }

    /**
     * Get the without_stock setting value.
     *
     * @return bool
     */
    protected function isWithoutStockEnabled(): bool
    {
        $generalSetting = $this->getGeneralSettings();

        return $generalSetting && $generalSetting->without_stock === 'yes';
    }
}

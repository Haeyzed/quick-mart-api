<?php

declare(strict_types=1);

namespace App\Traits;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Auto Update Trait
 *
 * Provides methods for checking application version updates and retrieving upgrade files.
 * This trait can be used by controllers or services that need to check for available updates.
 *
 * @package App\Traits
 */
trait AutoUpdateTrait
{
    /**
     * Check if an update is available for the application.
     *
     * This method compares the current application version with the latest available version
     * from the update server and determines if an upgrade is available and allowed.
     *
     * @return array<string, mixed> Update information containing:
     *   - 'alert_version_upgrade_enable': bool - Whether upgrade alert should be shown
     *   - 'demo_version': string|null - Latest demo version available
     *   - 'latest_version_db_migrate_enable': bool|null - Whether DB migration is needed
     *   - 'advertise_info': mixed|null - Advertisement information
     */
    public function isUpdateAvailable(): array
    {
        $versionUpgradeData = [
            'alert_version_upgrade_enable' => false,
            'demo_version' => null,
            'latest_version_db_migrate_enable' => false,
            'advertise_info' => null,
        ];

        try {
            $url = $this->getUpdateCheckUrl();
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get($url);

            if (!$response->successful()) {
                Log::warning('AutoUpdateTrait: Failed to check for updates', [
                    'status' => $response->status(),
                ]);
                return $versionUpgradeData;
            }

            $data = $response->json();

            if (empty($data)) {
                return $versionUpgradeData;
            }

            $clientVersionNumber = $this->stringToNumberConvert(config('app.version', '1.0.0'));
            $demoVersionNumber = $this->stringToNumberConvert($data['demo_version'] ?? '0.0.0');
            $minimumRequiredVersion = $this->stringToNumberConvert($data['minimum_required_version'] ?? '0.0.0');

            $versionUpgradeData['alert_version_upgrade_enable'] = (
                $demoVersionNumber > $clientVersionNumber &&
                $clientVersionNumber >= $minimumRequiredVersion
            );
            $versionUpgradeData['demo_version'] = $data['demo_version'] ?? null;
            $versionUpgradeData['latest_version_db_migrate_enable'] = $data['latest_version_db_migrate_enable'] ?? false;
            $versionUpgradeData['advertise_info'] = $data['advertise_info'] ?? null;
        } catch (Exception $e) {
            Log::error('AutoUpdateTrait: Exception while checking for updates', [
                'message' => $e->getMessage(),
            ]);
        }

        return $versionUpgradeData;
    }

    /**
     * Get the update check URL based on application type.
     *
     * @return string The update check API endpoint URL
     */
    private function getUpdateCheckUrl(): string
    {
        // Check if this is a SaaS installation
        $isSaaS = config('database.connections.saleprosaas_landlord') !== null;

        return $isSaaS
            ? 'https://lion-coders.com/api/sale-pro-saas-purchase/verify/updatecheck'
            : 'https://lion-coders.com/api/sale-pro-purchase/verify/updatecheck';
    }

    /**
     * Convert version string to numeric value for comparison.
     *
     * @param string $versionString Version string (e.g., "1.2.3")
     * @return int Numeric representation of the version
     */
    private function stringToNumberConvert(string $versionString): int
    {
        $parts = explode('.', $versionString);
        $versionString = '';

        foreach ($parts as $part) {
            $versionString .= $part;
        }

        return (int)$versionString;
    }

    /**
     * Get the version upgrade file URL for a given purchase code.
     *
     * @param string $purchaseCode The purchase code/license key
     * @return string|null The URL to download the upgrade file, or null if unavailable
     */
    public function versionUpgradeFileUrl(string $purchaseCode): ?string
    {
        try {
            $url = $this->getUpdateFileUrl($purchaseCode);
            $response = Http::timeout(10)
                ->withoutVerifying()
                ->get($url);

            if (!$response->successful()) {
                Log::warning('AutoUpdateTrait: Failed to get upgrade file URL', [
                    'status' => $response->status(),
                ]);
                return null;
            }

            $data = $response->json();

            return $data['version_upgrade_file_url'] ?? null;
        } catch (Exception $e) {
            Log::error('AutoUpdateTrait: Exception while getting upgrade file URL', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Get the update file URL for a purchase code.
     *
     * @param string $purchaseCode The purchase code
     * @return string The update file API endpoint URL
     */
    private function getUpdateFileUrl(string $purchaseCode): string
    {
        $isSaaS = config('database.connections.saleprosaas_landlord') !== null;
        $encodedCode = urlencode($purchaseCode);

        return $isSaaS
            ? "https://lion-coders.com/api/sale-pro-saas-purchase/verify/updatefile/{$encodedCode}"
            : "https://lion-coders.com/api/sale-pro-purchase/verify/updatefile/{$encodedCode}";
    }
}


<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExternalService;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class for SMS Setting operations.
 *
 * Centralizes business logic for SMS provider configuration (ExternalService type=sms).
 */
class SmsSettingService extends BaseService
{
    use CheckPermissionsTrait;

    private const SMS_TYPE = 'sms';

    /**
     * SmsSettingService constructor.
     */
    public function __construct()
    {
    }

    /**
     * Retrieve all SMS providers.
     *
     * Requires sms_setting permission.
     *
     * @return Collection<int, ExternalService>
     */
    public function getSmsProviders(): Collection
    {
        $this->requirePermission('sms_setting');

        return ExternalService::where('type', self::SMS_TYPE)
            ->orderBy('name')
            ->get();
    }

    /**
     * Retrieve a single SMS provider by ID.
     *
     * Requires sms_setting permission.
     *
     * @param int $id External service ID.
     * @return ExternalService|null The SMS provider or null if not found.
     */
    public function getSmsProvider(int $id): ?ExternalService
    {
        $this->requirePermission('sms_setting');

        return ExternalService::where('type', self::SMS_TYPE)->find($id);
    }

    /**
     * Update an SMS provider configuration.
     *
     * Requires sms_setting permission.
     *
     * @param int $id External service ID.
     * @param array<string, mixed> $data Validated data (details, active).
     * @return ExternalService The updated SMS provider instance.
     */
    public function updateSmsProvider(int $id, array $data): ExternalService
    {
        $this->requirePermission('sms_setting');

        $provider = ExternalService::where('type', self::SMS_TYPE)->findOrFail($id);

        if (isset($data['details']) && is_array($data['details'])) {
            $existingDetails = is_string($provider->details)
                ? json_decode($provider->details, true) ?? []
                : (array)$provider->details;

            $provider->details = json_encode(array_merge($existingDetails, $data['details']));
        }

        if (array_key_exists('active', $data)) {
            $provider->active = (bool)$data['active'];
            if ($provider->active) {
                ExternalService::where('type', self::SMS_TYPE)
                    ->whereKeyNot($id)
                    ->update(['active' => false]);
            }
        }

        $provider->save();

        return $provider->fresh();
    }
}

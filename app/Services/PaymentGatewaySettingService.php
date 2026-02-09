<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ExternalService;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Database\Eloquent\Collection;

/**
 * Service class for Payment Gateway Setting operations.
 *
 * Centralizes business logic for payment gateway configuration (ExternalService type=payment).
 */
class PaymentGatewaySettingService extends BaseService
{
    use CheckPermissionsTrait;

    private const PAYMENT_TYPE = 'payment';

    /**
     * PaymentGatewaySettingService constructor.
     */
    public function __construct() {}

    /**
     * Retrieve all payment gateways.
     *
     * Requires payment_gateway_setting permission.
     *
     * @return Collection<int, ExternalService>
     */
    public function getPaymentGateways(): Collection
    {
        $this->requirePermission('payment_gateway_setting');

        return ExternalService::where('type', self::PAYMENT_TYPE)->orderBy('name')->get();
    }

    /**
     * Retrieve a single payment gateway by ID.
     *
     * Requires payment_gateway_setting permission.
     *
     * @param  int  $id  External service ID.
     * @return ExternalService|null The payment gateway or null if not found.
     */
    public function getPaymentGateway(int $id): ?ExternalService
    {
        $this->requirePermission('payment_gateway_setting');

        return ExternalService::where('type', self::PAYMENT_TYPE)->find($id);
    }

    /**
     * Update a payment gateway configuration.
     *
     * Requires payment_gateway_setting permission.
     *
     * @param  int  $id  External service ID.
     * @param  array<string, mixed>  $data  Validated data (details, active, module_status).
     * @return ExternalService The updated payment gateway instance.
     */
    public function updatePaymentGateway(int $id, array $data): ExternalService
    {
        $this->requirePermission('payment_gateway_setting');

        $gateway = ExternalService::where('type', self::PAYMENT_TYPE)->findOrFail($id);

        if (isset($data['details']) && is_array($data['details'])) {
            $gateway->details = $this->encodeDetails($gateway->details, $data['details']);
        }

        if (array_key_exists('active', $data)) {
            $gateway->active = (bool) $data['active'];
        }

        if (isset($data['module_status']) && is_array($data['module_status'])) {
            $existing = is_string($gateway->module_status) ? json_decode($gateway->module_status, true) ?? [] : (array) $gateway->module_status;
            $gateway->module_status = json_encode(array_merge($existing, $data['module_status']));
        }

        $gateway->save();

        return $gateway->fresh();
    }

    /**
     * Merge existing details with incoming and encode.
     *
     * @param  string|null  $existing  Existing encoded details string.
     * @param  array<string, string>  $incoming  New details to merge.
     * @return string Encoded details string.
     */
    private function encodeDetails(?string $existing, array $incoming): string
    {
        $parsed = $this->parseDetails($existing);
        $merged = array_merge($parsed, $incoming);

        return implode(',', array_keys($merged)).';'.implode(',', array_values($merged));
    }

    /**
     * Parse encoded details string into associative array.
     *
     * @param  string|null  $details  Encoded string (keys;values format).
     * @return array<string, string> Parsed details.
     */
    public function parseDetails(?string $details): array
    {
        if (empty($details) || ! str_contains($details, ';')) {
            return [];
        }
        [$keysStr, $valsStr] = explode(';', $details, 2);
        $keys = array_map('trim', explode(',', $keysStr));
        $vals = array_map('trim', explode(',', $valsStr));

        return array_combine($keys, $vals) ?: [];
    }
}

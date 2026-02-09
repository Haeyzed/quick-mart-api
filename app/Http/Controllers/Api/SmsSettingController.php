<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\SmsSettingRequest;
use App\Http\Resources\SmsSettingResource;
use App\Services\SmsSettingService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for SMS Setting.
 *
 * Handles SMS provider configuration (stored in external_services).
 *
 * @group SMS Setting
 */
class SmsSettingController extends Controller
{
    public function __construct(
        private readonly SmsSettingService $service
    ) {}

    /**
     * List all SMS providers.
     *
     * @return JsonResponse Paginated SMS providers.
     */
    public function index(): JsonResponse
    {
        $providers = $this->service->getSmsProviders();

        return response()->success(
            SmsSettingResource::collection($providers),
            'SMS providers retrieved successfully'
        );
    }

    /**
     * Display a single SMS provider.
     *
     * @param int $id External service ID.
     * @return JsonResponse The SMS provider or 404.
     */
    public function show(int $id): JsonResponse
    {
        $provider = $this->service->getSmsProvider($id);

        if (! $provider) {
            return response()->notFound('SMS provider not found');
        }

        return response()->success(
            new SmsSettingResource($provider),
            'SMS provider retrieved successfully'
        );
    }

    /**
     * Update an SMS provider.
     *
     * @param SmsSettingRequest $request Validated provider data.
     * @param int $id External service ID.
     * @return JsonResponse The updated SMS provider.
     */
    public function update(SmsSettingRequest $request, int $id): JsonResponse
    {
        $provider = $this->service->updateSmsProvider($id, $request->validated());

        return response()->success(
            new SmsSettingResource($provider),
            'SMS provider updated successfully'
        );
    }
}

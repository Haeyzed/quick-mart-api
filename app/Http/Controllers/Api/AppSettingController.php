<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\MobileTokenResource;
use App\Services\AppSettingService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for App Setting.
 *
 * Handles mobile app connection info and active device tokens.
 *
 * @group App Setting
 */
class AppSettingController extends Controller
{
    public function __construct(
        private readonly AppSettingService $service
    )
    {
    }

    /**
     * Display app setting (install URL, app key, QR payload, active devices).
     */
    public function show(): JsonResponse
    {
        $data = $this->service->getAppSetting();

        return response()->success([
            'install_url' => $data['install_url'],
            'app_key' => $data['app_key'],
            'qr_code_payload' => $data['qr_code_payload'],
            'mobile_tokens' => MobileTokenResource::collection($data['mobile_tokens']),
        ], 'App setting retrieved successfully');
    }

    /**
     * Revoke (deactivate) a mobile token.
     */
    public function destroyToken(int $id): JsonResponse
    {
        $this->service->deleteToken($id);

        return response()->success(null, 'Token revoked successfully');
    }
}

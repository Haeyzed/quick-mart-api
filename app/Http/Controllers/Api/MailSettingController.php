<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MailSettingRequest;
use App\Http\Resources\MailSettingResource;
use App\Services\MailSettingService;
use Illuminate\Http\JsonResponse;

/**
 * Class MailSettingController
 *
 * API Controller for Mail Setting.
 * Handles authorization via Policy and delegates logic to MailSettingService.
 *
 * @tags Mail Setting
 */
class MailSettingController extends Controller
{
    /**
     * MailSettingController constructor.
     */
    public function __construct(
        private readonly MailSettingService $service
    ) {}

    /**
     * Display the mail setting.
     *
     * Retrieve the current SMTP configuration.
     */
    public function show(): JsonResponse
    {
        if (auth()->user()->denies('manage mail settings')) {
            return response()->forbidden('Permission denied for viewing mail settings.');
        }

        $setting = $this->service->getMailSetting();

        return response()->success(
            $setting ? new MailSettingResource($setting) : null,
            $setting ? 'Mail setting retrieved successfully' : 'No mail setting configured'
        );
    }

    /**
     * Update the mail setting.
     *
     * Update SMTP configuration and optionally send a test email.
     */
    public function update(MailSettingRequest $request): JsonResponse
    {
        if (auth()->user()->denies('manage mail settings')) {
            return response()->forbidden('Permission denied for updating mail settings.');
        }

        $setting = $this->service->updateMailSetting(
            $request->validated(),
            $request->boolean('send_test')
        );

        return response()->success(
            new MailSettingResource($setting),
            'Mail setting updated successfully'
        );
    }

    /**
     * Send Test Email
     *
     * Send a test email using current mail settings to the configured from_address.
     */
    public function test(): JsonResponse
    {
        if (auth()->user()->denies('manage mail settings')) {
            return response()->forbidden('Permission denied for testing mail settings.');
        }

        $this->service->sendTestEmail();

        return response()->success(null, 'Test email sent successfully');
    }
}

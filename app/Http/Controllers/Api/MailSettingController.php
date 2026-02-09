<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\MailSettingRequest;
use App\Http\Resources\MailSettingResource;
use App\Services\MailSettingService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Mail Setting.
 *
 * Handles SMTP configuration and test email.
 *
 * @group Mail Setting
 */
class MailSettingController extends Controller
{
    /**
     * MailSettingController constructor.
     *
     * @param MailSettingService $service
     */
    public function __construct(
        private readonly MailSettingService $service
    ) {}

    /**
     * Display the mail setting.
     *
     * @return JsonResponse The mail setting or empty response if not configured.
     */
    public function show(): JsonResponse
    {
        $setting = $this->service->getMailSetting();

        if (! $setting) {
            return response()->success(null, 'No mail setting configured');
        }

        return response()->success(
            new MailSettingResource($setting),
            'Mail setting retrieved successfully'
        );
    }

    /**
     * Update the mail setting.
     *
     * Optionally sends test email when send_test is true.
     *
     * @param MailSettingRequest $request Validated SMTP configuration.
     * @return JsonResponse The updated mail setting.
     */
    public function update(MailSettingRequest $request): JsonResponse
    {
        $sendTest = $request->boolean('send_test');

        $setting = $this->service->updateMailSetting($request->validated(), $sendTest);

        $message = $sendTest
            ? 'Mail setting updated and test email sent to ' . $setting->from_address
            : 'Mail setting updated successfully';

        return response()->success(
            new MailSettingResource($setting),
            $message
        );
    }

    /**
     * Send a test email using current mail settings.
     *
     * @return JsonResponse Success message.
     */
    public function test(): JsonResponse
    {
        $this->service->sendTestEmail();

        return response()->success(null, 'Test email sent successfully');
    }
}

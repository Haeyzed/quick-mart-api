<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SendSmsRequest;
use App\Http\Resources\SmsTemplateResource;
use App\Services\CreateSmsService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Create/Send SMS.
 *
 * Handles listing templates and sending SMS messages.
 *
 * @group Create/Send SMS
 */
class CreateSmsController extends Controller
{
    /**
     * CreateSmsController constructor.
     *
     * @param CreateSmsService $service
     */
    public function __construct(
        private readonly CreateSmsService $service
    ) {}

    /**
     * List SMS templates.
     *
     * @return JsonResponse Collection of SMS templates.
     */
    public function index(): JsonResponse
    {
        $templates = $this->service->getTemplates();

        return response()->success(
            SmsTemplateResource::collection($templates),
            'SMS templates retrieved successfully'
        );
    }

    /**
     * Send an SMS.
     *
     * Requires recipient and either message or template_id (with optional placeholders).
     *
     * @param SendSmsRequest $request Validated recipient, message/template, and placeholders.
     * @return JsonResponse Success or error response.
     */
    public function send(SendSmsRequest $request): JsonResponse
    {
        try {
            $result = $this->service->sendSms($request->validated());
        } catch (\InvalidArgumentException $e) {
            return response()->error($e->getMessage(), \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\RuntimeException $e) {
            return response()->error($e->getMessage(), \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($result === false) {
            return response()->error(
                'SMS could not be sent. Please check your SMS provider configuration.',
                \Illuminate\Http\Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        return response()->success(
            is_array($result) ? $result : ['sent' => true],
            'SMS sent successfully'
        );
    }
}

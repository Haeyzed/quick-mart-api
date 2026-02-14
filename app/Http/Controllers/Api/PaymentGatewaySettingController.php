<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\PaymentGatewayRequest;
use App\Http\Resources\PaymentGatewayResource;
use App\Services\PaymentGatewaySettingService;
use Illuminate\Http\JsonResponse;

/**
 * API Controller for Payment Gateway Setting.
 *
 * Handles payment gateway configuration (stored in external_services).
 *
 * @group Payment Gateway Setting
 */
class PaymentGatewaySettingController extends Controller
{
    /**
     * PaymentGatewaySettingController constructor.
     *
     * @param PaymentGatewaySettingService $service
     */
    public function __construct(
        private readonly PaymentGatewaySettingService $service
    )
    {
    }

    /**
     * List all payment gateways.
     *
     * @return JsonResponse Payment gateways collection.
     */
    public function index(): JsonResponse
    {
        $gateways = $this->service->getPaymentGateways();

        return response()->success(
            PaymentGatewayResource::collection($gateways),
            'Payment gateways retrieved successfully'
        );
    }

    /**
     * Display a single payment gateway.
     *
     * @param int $id External service ID.
     * @return JsonResponse The payment gateway or 404.
     */
    public function show(int $id): JsonResponse
    {
        $gateway = $this->service->getPaymentGateway($id);

        if (!$gateway) {
            return response()->notFound('Payment gateway not found');
        }

        return response()->success(
            new PaymentGatewayResource($gateway),
            'Payment gateway retrieved successfully'
        );
    }

    /**
     * Update a payment gateway.
     *
     * @param PaymentGatewayRequest $request Validated gateway data.
     * @param int $id External service ID.
     * @return JsonResponse The updated payment gateway.
     */
    public function update(PaymentGatewayRequest $request, int $id): JsonResponse
    {
        $gateway = $this->service->updatePaymentGateway($id, $request->validated());

        return response()->success(
            new PaymentGatewayResource($gateway),
            'Payment gateway updated successfully'
        );
    }
}

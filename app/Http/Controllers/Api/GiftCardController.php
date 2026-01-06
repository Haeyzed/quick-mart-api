<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\GiftCardBulkDestroyRequest;
use App\Http\Requests\GiftCardIndexRequest;
use App\Http\Requests\GiftCardRechargeRequest;
use App\Http\Requests\GiftCardRequest;
use App\Http\Resources\GiftCardResource;
use App\Models\GiftCard;
use App\Services\GiftCardService;
use Illuminate\Http\JsonResponse;

/**
 * GiftCardController
 *
 * API controller for managing gift cards with full CRUD operations.
 */
class GiftCardController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param GiftCardService $service
     */
    public function __construct(
        private readonly GiftCardService $service
    )
    {
    }

    /**
     * Display a paginated listing of gift cards.
     *
     * @param GiftCardIndexRequest $request
     * @return JsonResponse
     */
    public function index(GiftCardIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $giftCards = $this->service->getGiftCards($filters, $perPage)
            ->through(fn($giftCard) => new GiftCardResource($giftCard));

        return response()->success($giftCards, 'Gift cards fetched successfully');
    }

    /**
     * Store a newly created gift card.
     *
     * @param GiftCardRequest $request
     * @return JsonResponse
     */
    public function store(GiftCardRequest $request): JsonResponse
    {
        $giftCard = $this->service->createGiftCard($request->validated());

        return response()->success(
            new GiftCardResource($giftCard),
            'Gift card created successfully',
            201
        );
    }

    /**
     * Display the specified gift card.
     *
     * @param GiftCard $giftCard
     * @return JsonResponse
     */
    public function show(GiftCard $giftCard): JsonResponse
    {
        return response()->success(
            new GiftCardResource($giftCard),
            'Gift card retrieved successfully'
        );
    }

    /**
     * Update the specified gift card.
     *
     * @param GiftCardRequest $request
     * @param GiftCard $giftCard
     * @return JsonResponse
     */
    public function update(GiftCardRequest $request, GiftCard $giftCard): JsonResponse
    {
        $giftCard = $this->service->updateGiftCard($giftCard, $request->validated());

        return response()->success(
            new GiftCardResource($giftCard),
            'Gift card updated successfully'
        );
    }

    /**
     * Remove the specified gift card from storage.
     *
     * @param GiftCard $giftCard
     * @return JsonResponse
     */
    public function destroy(GiftCard $giftCard): JsonResponse
    {
        $this->service->deleteGiftCard($giftCard);

        return response()->success(null, 'Gift card deleted successfully');
    }

    /**
     * Bulk delete multiple gift cards.
     *
     * @param GiftCardBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(GiftCardBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteGiftCards($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} gift cards successfully"
        );
    }

    /**
     * Generate a unique 16-digit gift card code.
     *
     * @return JsonResponse
     */
    public function generateCode(): JsonResponse
    {
        $code = GiftCard::generateCode();

        return response()->success(
            ['card_no' => $code],
            'Gift card code generated successfully'
        );
    }

    /**
     * Recharge a gift card with additional amount.
     *
     * @param GiftCardRechargeRequest $request
     * @param GiftCard $giftCard
     * @return JsonResponse
     */
    public function recharge(GiftCardRechargeRequest $request, GiftCard $giftCard): JsonResponse
    {
        $amount = (float)$request->validated()['amount'];

        $giftCard = $this->service->rechargeGiftCard($giftCard, $amount);

        return response()->success(
            new GiftCardResource($giftCard),
            'Gift card recharged successfully'
        );
    }
}

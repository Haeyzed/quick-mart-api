<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CurrencyBulkDestroyRequest;
use App\Http\Requests\CurrencyIndexRequest;
use App\Http\Requests\CurrencyRequest;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;

/**
 * CurrencyController
 *
 * API controller for managing currencies with full CRUD operations.
 */
class CurrencyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param CurrencyService $service
     */
    public function __construct(
        private readonly CurrencyService $service
    )
    {
    }

    /**
     * Display a paginated listing of currencies.
     *
     * @param CurrencyIndexRequest $request
     * @return JsonResponse
     */
    public function index(CurrencyIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $currencies = $this->service->getCurrencies($filters, $perPage)
            ->through(fn($currency) => new CurrencyResource($currency));

        return response()->success($currencies, 'Currencies fetched successfully');
    }

    /**
     * Store a newly created currency.
     *
     * @param CurrencyRequest $request
     * @return JsonResponse
     */
    public function store(CurrencyRequest $request): JsonResponse
    {
        $currency = $this->service->createCurrency($request->validated());

        return response()->success(
            new CurrencyResource($currency),
            'Currency created successfully',
            201
        );
    }

    /**
     * Display the specified currency.
     *
     * @param Currency $currency
     * @return JsonResponse
     */
    public function show(Currency $currency): JsonResponse
    {
        return response()->success(
            new CurrencyResource($currency),
            'Currency retrieved successfully'
        );
    }

    /**
     * Update the specified currency.
     *
     * @param CurrencyRequest $request
     * @param Currency $currency
     * @return JsonResponse
     */
    public function update(CurrencyRequest $request, Currency $currency): JsonResponse
    {
        $currency = $this->service->updateCurrency($currency, $request->validated());

        return response()->success(
            new CurrencyResource($currency),
            'Currency updated successfully'
        );
    }

    /**
     * Remove the specified currency from storage.
     *
     * @param Currency $currency
     * @return JsonResponse
     */
    public function destroy(Currency $currency): JsonResponse
    {
        $this->service->deleteCurrency($currency);

        return response()->success(null, 'Currency deleted successfully');
    }

    /**
     * Bulk delete multiple currencies.
     *
     * @param CurrencyBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(CurrencyBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteCurrencies($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} currencies successfully"
        );
    }
}

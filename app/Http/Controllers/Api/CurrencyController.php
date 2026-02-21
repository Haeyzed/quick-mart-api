<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CurrencyController
 *
 * API Controller for Currency listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to CurrencyService.
 *
 * @tags Currency Management
 */
class CurrencyController extends Controller
{
    /**
     * CurrencyController constructor.
     *
     * @param  CurrencyService  $service  Service handling currency business logic.
     */
    public function __construct(
        private readonly CurrencyService $service
    ) {}

    /**
     * List Currencies
     *
     * Display a paginated listing of currencies. Supports searching and filtering by country.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view currencies')) {
            return response()->forbidden('Permission denied for viewing currencies list.');
        }

        $currencies = $this->service->getPaginatedCurrencies(
            $request->validate([
                /**
                 * Search term to filter currencies by name, code or symbol.
                 *
                 * @example "USD"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by country ID.
                 *
                 * @example 1
                 */
                'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            CurrencyResource::collection($currencies),
            'Currencies retrieved successfully'
        );
    }

    /**
     * Get currency options for select components (value/label format).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view currencies')) {
            return response()->forbidden('Permission denied for viewing currency options.');
        }

        return response()->success($this->service->getOptions(), 'Currency options retrieved successfully');
    }

    /**
     * Show Currency
     *
     * Display the specified currency.
     */
    public function show(Currency $currency): JsonResponse
    {
        if (auth()->user()->denies('view currencies')) {
            return response()->forbidden('Permission denied for view currency.');
        }

        return response()->success(
            new CurrencyResource($currency->load('country')),
            'Currency details retrieved successfully'
        );
    }
}

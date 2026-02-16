<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CurrencyResource;
use App\Models\Currency;
use App\Services\WorldCurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CurrencyController
 *
 * API Controller for Currency listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to WorldCurrencyService.
 *
 * @group Currency Management
 */
class CurrencyController extends Controller
{
    /**
     * CurrencyController constructor.
     */
    public function __construct(
        private readonly WorldCurrencyService $service
    ) {}

    /**
     * Display a paginated listing of currencies.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view currencies')) {
            return response()->forbidden('Permission denied for viewing currencies list.');
        }

        $currencies = $this->service->getPaginatedWorldCurrencies(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            CurrencyResource::collection($currencies),
            'Currencies retrieved successfully'
        );
    }

    /**
     * Get currency options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view currencies')) {
            return response()->forbidden('Permission denied for viewing currency options.');
        }

        return response()->success($this->service->getOptions(), 'Currency options retrieved successfully');
    }

    /**
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

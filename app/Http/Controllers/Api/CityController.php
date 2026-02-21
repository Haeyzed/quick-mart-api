<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Models\City;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CityController
 *
 * API Controller for City listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to CityService.
 *
 * @tags City Management
 */
class CityController extends Controller
{
    /**
     * CityController constructor.
     *
     * @param  CityService  $service  Service handling city business logic.
     */
    public function __construct(
        private readonly CityService $service
    ) {}

    /**
     * List Cities
     *
     * Display a paginated listing of cities. Supports searching and filtering by country or state.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for viewing cities list.');
        }

        $cities = $this->service->getPaginatedCities(
            $request->validate([
                /**
                 * Search term to filter cities by name.
                 *
                 * @example "New York"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by country ID.
                 *
                 * @example 1
                 */
                'country_id' => ['nullable', 'integer', 'exists:countries,id'],
                /**
                 * Filter by state ID.
                 *
                 * @example 1
                 */
                'state_id' => ['nullable', 'integer', 'exists:states,id'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            CityResource::collection($cities),
            'Cities retrieved successfully'
        );
    }

    /**
     * Get city options for select components (value/label format).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for viewing city options.');
        }

        return response()->success($this->service->getOptions(), 'City options retrieved successfully');
    }

    /**
     * Show City
     *
     * Display the specified city.
     */
    public function show(City $city): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for view city.');
        }

        return response()->success(
            new CityResource($city->load(['country', 'state'])),
            'City details retrieved successfully'
        );
    }
}

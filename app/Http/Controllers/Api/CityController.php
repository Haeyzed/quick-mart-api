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
 * @group City Management
 */
class CityController extends Controller
{
    /**
     * CityController constructor.
     */
    public function __construct(
        private readonly CityService $service
    ) {}

    /**
     * Display a paginated listing of cities.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for viewing cities list.');
        }

        $cities = $this->service->getPaginatedCities(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            CityResource::collection($cities),
            'Cities retrieved successfully'
        );
    }

    /**
     * Get city options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for viewing city options.');
        }

        return response()->success($this->service->getOptions(), 'City options retrieved successfully');
    }

    /**
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

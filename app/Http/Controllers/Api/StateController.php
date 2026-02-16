<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CityResource;
use App\Http\Resources\StateResource;
use App\Models\State;
use App\Services\StateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class StateController
 *
 * API Controller for State listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to StateService.
 *
 * @group State Management
 */
class StateController extends Controller
{
    /**
     * StateController constructor.
     */
    public function __construct(
        private readonly StateService $service
    ) {}

    /**
     * Display a paginated listing of states.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for viewing states list.');
        }

        $states = $this->service->getPaginatedStates(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            StateResource::collection($states),
            'States retrieved successfully'
        );
    }

    /**
     * Get state options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for viewing state options.');
        }

        return response()->success($this->service->getOptions(), 'State options retrieved successfully');
    }

    /**
     * Display the specified state.
     */
    public function show(State $state): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for view state.');
        }

        return response()->success(
            new StateResource($state->load('country')),
            'State details retrieved successfully'
        );
    }

    /**
     * Get cities for the specified state.
     */
    public function cities(State $state): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for viewing cities by state.');
        }

        $cities = $this->service->getCitiesByState($state);

        return response()->success(
            CityResource::collection($cities),
            'Cities retrieved successfully'
        );
    }
}

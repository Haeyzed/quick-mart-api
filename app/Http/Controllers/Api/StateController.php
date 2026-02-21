<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
 * @tags State Management
 */
class StateController extends Controller
{
    /**
     * StateController constructor.
     *
     * @param  StateService  $service  Service handling state business logic.
     */
    public function __construct(
        private readonly StateService $service
    ) {}

    /**
     * List States
     *
     * Display a paginated listing of states. Supports searching and filtering by country.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for viewing states list.');
        }

        $states = $this->service->getPaginatedStates(
            $request->validate([
                /**
                 * Search term to filter states by name or state_code.
                 *
                 * @example "California"
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
            StateResource::collection($states),
            'States retrieved successfully'
        );
    }

    /**
     * Get state options for select components (value/label format).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for viewing state options.');
        }

        return response()->success($this->service->getOptions(), 'State options retrieved successfully');
    }

    /**
     * Show State
     *
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
     * Get city options (value/label) for the specified state.
     *
     * @param  State  $state  State model (route binding).
     */
    public function cities(State $state): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for viewing cities by state.');
        }

        $options = $this->service->getCityOptionsByState($state);

        return response()->success($options, 'Cities retrieved successfully');
    }
}

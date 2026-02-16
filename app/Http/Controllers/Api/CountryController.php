<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CountryResource;
use App\Models\Country;
use App\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class CountryController
 *
 * API Controller for Country listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to CountryService.
 *
 * @group Country Management
 */
class CountryController extends Controller
{
    /**
     * CountryController constructor.
     */
    public function __construct(
        private readonly CountryService $service
    ) {}

    /**
     * Display a paginated listing of countries.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view countries')) {
            return response()->forbidden('Permission denied for viewing countries list.');
        }

        $countries = $this->service->getPaginatedCountries(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            CountryResource::collection($countries),
            'Countries retrieved successfully'
        );
    }

    /**
     * Get country options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view countries')) {
            return response()->forbidden('Permission denied for viewing country options.');
        }

        return response()->success($this->service->getOptions(), 'Country options retrieved successfully');
    }

    /**
     * Display the specified country.
     */
    public function show(Country $country): JsonResponse
    {
        if (auth()->user()->denies('view countries')) {
            return response()->forbidden('Permission denied for view country.');
        }

        return response()->success(
            new CountryResource($country),
            'Country details retrieved successfully'
        );
    }

    /**
     * Get state options (value/label) for the specified country.
     */
    public function states(Country $country): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for viewing states by country.');
        }

        $options = $this->service->getStateOptionsByCountry($country);

        return response()->success($options, 'States retrieved successfully');
    }
}

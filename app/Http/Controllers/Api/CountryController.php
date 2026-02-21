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
 * @tags Country Management
 */
class CountryController extends Controller
{
    /**
     * CountryController constructor.
     *
     * @param  CountryService  $service  Service handling country business logic.
     */
    public function __construct(
        private readonly CountryService $service
    ) {}

    /**
     * List Countries
     *
     * Display a paginated listing of countries. Supports searching by name, iso2 or iso3.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view countries')) {
            return response()->forbidden('Permission denied for viewing countries list.');
        }

        $countries = $this->service->getPaginatedCountries(
            $request->validate([
                /**
                 * Search term to filter countries by name, iso2 or iso3.
                 *
                 * @example "United"
                 */
                'search' => ['nullable', 'string'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            CountryResource::collection($countries),
            'Countries retrieved successfully'
        );
    }

    /**
     * Get country options for select components (value/label format).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view countries')) {
            return response()->forbidden('Permission denied for viewing country options.');
        }

        return response()->success($this->service->getOptions(), 'Country options retrieved successfully');
    }

    /**
     * Show Country
     *
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
     *
     * @param  Country  $country  Country model (route binding).
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

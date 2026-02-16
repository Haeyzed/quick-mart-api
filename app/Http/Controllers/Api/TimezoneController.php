<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TimezoneResource;
use App\Models\Timezone;
use App\Services\TimezoneService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Class TimezoneController
 *
 * API Controller for Timezone listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to TimezoneService.
 *
 * @group Timezone Management
 */
class TimezoneController extends Controller
{
    /**
     * TimezoneController constructor.
     */
    public function __construct(
        private readonly TimezoneService $service
    ) {}

    /**
     * Display a paginated listing of timezones.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view timezones')) {
            return response()->forbidden('Permission denied for viewing timezones list.');
        }

        $timezones = $this->service->getPaginatedTimezones(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            TimezoneResource::collection($timezones),
            'Timezones retrieved successfully'
        );
    }

    /**
     * Get timezone options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view timezones')) {
            return response()->forbidden('Permission denied for viewing timezone options.');
        }

        return response()->success($this->service->getOptions(), 'Timezone options retrieved successfully');
    }

    /**
     * Display the specified timezone.
     */
    public function show(Timezone $timezone): JsonResponse
    {
        if (auth()->user()->denies('view timezones')) {
            return response()->forbidden('Permission denied for view timezone.');
        }

        return response()->success(
            new TimezoneResource($timezone->load('country')),
            'Timezone details retrieved successfully'
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cities\CityBulkActionRequest;
use App\Http\Requests\Cities\StoreCityRequest;
use App\Http\Requests\Cities\UpdateCityRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CityResource;
use App\Mail\ExportMail;
use App\Models\City;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\CityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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

    /**
     * Create City
     */
    public function store(StoreCityRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create cities')) {
            return response()->forbidden('Permission denied for create city.');
        }

        $city = $this->service->createCity($request->validated());

        return response()->success(
            new CityResource($city),
            'City created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update City
     */
    public function update(UpdateCityRequest $request, City $city): JsonResponse
    {
        if (auth()->user()->denies('update cities')) {
            return response()->forbidden('Permission denied for update city.');
        }

        $updatedCity = $this->service->updateCity($city, $request->validated());

        return response()->success(
            new CityResource($updatedCity),
            'City updated successfully'
        );
    }

    /**
     * Delete City
     */
    public function destroy(City $city): JsonResponse
    {
        if (auth()->user()->denies('delete cities')) {
            return response()->forbidden('Permission denied for delete city.');
        }

        $this->service->deleteCity($city);

        return response()->success(null, 'City deleted successfully');
    }

    /**
     * Bulk Delete Cities
     */
    public function bulkDestroy(CityBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete cities')) {
            return response()->forbidden('Permission denied for bulk delete cities.');
        }

        $count = $this->service->bulkDeleteCities($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} cities"
        );
    }

    /**
     * Import Cities
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import cities')) {
            return response()->forbidden('Permission denied for import cities.');
        }

        $this->service->importCities($request->file('file'));

        return response()->success(null, 'Cities imported successfully');
    }

    /**
     * Export Cities
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export cities')) {
            return response()->forbidden('Permission denied for export cities.');
        }

        $validated = $request->validated();

        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (! $user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (! $mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'cities_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your City Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: '.$user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download Import Template
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import cities')) {
            return response()->forbidden('Permission denied for downloading cities import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

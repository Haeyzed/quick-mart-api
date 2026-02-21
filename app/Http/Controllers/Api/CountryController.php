<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Countries\CountryBulkActionRequest;
use App\Http\Requests\Countries\StoreCountryRequest;
use App\Http\Requests\Countries\UpdateCountryRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CountryResource;
use App\Mail\ExportMail;
use App\Models\Country;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\CountryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

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

    /**
     * Create Country
     */
    public function store(StoreCountryRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create countries')) {
            return response()->forbidden('Permission denied for create country.');
        }

        $country = $this->service->createCountry($request->validated());

        return response()->success(
            new CountryResource($country),
            'Country created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update Country
     */
    public function update(UpdateCountryRequest $request, Country $country): JsonResponse
    {
        if (auth()->user()->denies('update countries')) {
            return response()->forbidden('Permission denied for update country.');
        }

        $updatedCountry = $this->service->updateCountry($country, $request->validated());

        return response()->success(
            new CountryResource($updatedCountry),
            'Country updated successfully'
        );
    }

    /**
     * Delete Country
     */
    public function destroy(Country $country): JsonResponse
    {
        if (auth()->user()->denies('delete countries')) {
            return response()->forbidden('Permission denied for delete country.');
        }

        $this->service->deleteCountry($country);

        return response()->success(null, 'Country deleted successfully');
    }

    /**
     * Bulk Delete Countries
     */
    public function bulkDestroy(CountryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete countries')) {
            return response()->forbidden('Permission denied for bulk delete countries.');
        }

        $count = $this->service->bulkDeleteCountries($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} countries"
        );
    }

    /**
     * Bulk Activate Countries
     */
    public function bulkActivate(CountryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update countries')) {
            return response()->forbidden('Permission denied for bulk update countries.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} countries activated"
        );
    }

    /**
     * Bulk Deactivate Countries
     */
    public function bulkDeactivate(CountryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update countries')) {
            return response()->forbidden('Permission denied for bulk update countries.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} countries deactivated"
        );
    }

    /**
     * Import Countries
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import countries')) {
            return response()->forbidden('Permission denied for import countries.');
        }

        $this->service->importCountries($request->file('file'));

        return response()->success(null, 'Countries imported successfully');
    }

    /**
     * Export Countries
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export countries')) {
            return response()->forbidden('Permission denied for export countries.');
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
                    'countries_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Country Export Is Ready',
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
        if (auth()->user()->denies('import countries')) {
            return response()->forbidden('Permission denied for downloading countries import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Currencies\CurrencyBulkActionRequest;
use App\Http\Requests\Currencies\StoreCurrencyRequest;
use App\Http\Requests\Currencies\UpdateCurrencyRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CurrencyResource;
use App\Mail\ExportMail;
use App\Models\Currency;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\CurrencyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class CurrencyController
 *
 * API Controller for Currency listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to CurrencyService.
 *
 * @tags Currency Management
 */
class CurrencyController extends Controller
{
    /**
     * CurrencyController constructor.
     *
     * @param  CurrencyService  $service  Service handling currency business logic.
     */
    public function __construct(
        private readonly CurrencyService $service
    ) {}

    /**
     * List Currencies
     *
     * Display a paginated listing of currencies. Supports searching and filtering by country.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view currencies')) {
            return response()->forbidden('Permission denied for viewing currencies list.');
        }

        $currencies = $this->service->getPaginatedCurrencies(
            $request->validate([
                /**
                 * Search term to filter currencies by name, code or symbol.
                 *
                 * @example "USD"
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
            CurrencyResource::collection($currencies),
            'Currencies retrieved successfully'
        );
    }

    /**
     * Get currency options for select components (value/label format).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view currencies')) {
            return response()->forbidden('Permission denied for viewing currency options.');
        }

        return response()->success($this->service->getOptions(), 'Currency options retrieved successfully');
    }

    /**
     * Show Currency
     *
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

    /**
     * Create Currency
     */
    public function store(StoreCurrencyRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create currencies')) {
            return response()->forbidden('Permission denied for create currency.');
        }

        $currency = $this->service->createCurrency($request->validated());

        return response()->success(
            new CurrencyResource($currency),
            'Currency created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update Currency
     */
    public function update(UpdateCurrencyRequest $request, Currency $currency): JsonResponse
    {
        if (auth()->user()->denies('update currencies')) {
            return response()->forbidden('Permission denied for update currency.');
        }

        $updatedCurrency = $this->service->updateCurrency($currency, $request->validated());

        return response()->success(
            new CurrencyResource($updatedCurrency),
            'Currency updated successfully'
        );
    }

    /**
     * Delete Currency
     */
    public function destroy(Currency $currency): JsonResponse
    {
        if (auth()->user()->denies('delete currencies')) {
            return response()->forbidden('Permission denied for delete currency.');
        }

        $this->service->deleteCurrency($currency);

        return response()->success(null, 'Currency deleted successfully');
    }

    /**
     * Bulk Delete Currencies
     */
    public function bulkDestroy(CurrencyBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete currencies')) {
            return response()->forbidden('Permission denied for bulk delete currencies.');
        }

        $count = $this->service->bulkDeleteCurrencies($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} currencies"
        );
    }

    /**
     * Import Currencies
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import currencies')) {
            return response()->forbidden('Permission denied for import currencies.');
        }

        $this->service->importCurrencies($request->file('file'));

        return response()->success(null, 'Currencies imported successfully');
    }

    /**
     * Export Currencies
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export currencies')) {
            return response()->forbidden('Permission denied for export currencies.');
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
                    'currencies_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Currency Export Is Ready',
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
        if (auth()->user()->denies('import currencies')) {
            return response()->forbidden('Permission denied for downloading currencies import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

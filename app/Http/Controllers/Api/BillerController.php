<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billers\BillerBulkActionRequest;
use App\Http\Requests\Billers\StoreBillerRequest;
use App\Http\Requests\Billers\UpdateBillerRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\BillerResource;
use App\Mail\ExportMail;
use App\Models\Biller;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\BillerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class BillerController
 *
 * API Controller for Biller CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to BillerService.
 *
 * @tags Biller Management
 */
class BillerController extends Controller
{
    /**
     * BillerController constructor.
     */
    public function __construct(
        private readonly BillerService $service
    ) {}

    /**
     * List Billers
     *
     * Display a paginated listing of billers. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view billers')) {
            return response()->forbidden('Permission denied for viewing billers list.');
        }

        $billers = $this->service->getPaginatedBillers(
            $request->validate([
                /**
                 * Search term to filter billers by name, email, phone, or company.
                 *
                 * @example "John Doe"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter billers starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter billers up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            ]),
            /**
             * Amount of items per page.
             *
             * @example 50
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            BillerResource::collection($billers),
            'Billers retrieved successfully'
        );
    }

    /**
     * Get Biller Options
     *
     * Retrieve a simplified list of active billers for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view billers')) {
            return response()->forbidden('Permission denied for viewing billers options.');
        }

        return response()->success($this->service->getOptions(), 'Biller options retrieved successfully');
    }

    /**
     * Create Biller
     *
     * Store a newly created biller in the system.
     */
    public function store(StoreBillerRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create billers')) {
            return response()->forbidden('Permission denied for create biller.');
        }

        $biller = $this->service->createBiller($request->validated());

        return response()->success(
            new BillerResource($biller),
            'Biller created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Biller
     *
     * Retrieve the details of a specific biller by its ID.
     */
    public function show(Biller $biller): JsonResponse
    {
        if (auth()->user()->denies('view biller details')) {
            return response()->forbidden('Permission denied for view biller.');
        }

        return response()->success(
            new BillerResource($biller->load(['country', 'state', 'city'])),
            'Biller details retrieved successfully'
        );
    }

    /**
     * Update Biller
     *
     * Update the specified biller's information.
     */
    public function update(UpdateBillerRequest $request, Biller $biller): JsonResponse
    {
        if (auth()->user()->denies('update billers')) {
            return response()->forbidden('Permission denied for update biller.');
        }

        $updatedBiller = $this->service->updateBiller($biller, $request->validated());

        return response()->success(
            new BillerResource($updatedBiller->load(['country', 'state', 'city'])),
            'Biller updated successfully'
        );
    }

    /**
     * Delete Biller
     *
     * Remove the specified biller from storage.
     */
    public function destroy(Biller $biller): JsonResponse
    {
        if (auth()->user()->denies('delete billers')) {
            return response()->forbidden('Permission denied for delete biller.');
        }

        $this->service->deleteBiller($biller);

        return response()->success(null, 'Biller deleted successfully');
    }

    /**
     * Bulk Delete Billers
     *
     * Delete multiple billers simultaneously using an array of IDs.
     */
    public function bulkDestroy(BillerBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete billers')) {
            return response()->forbidden('Permission denied for bulk delete billers.');
        }

        $count = $this->service->bulkDeleteBillers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} billers"
        );
    }

    /**
     * Bulk Activate Billers
     *
     * Set the active status of multiple billers to true.
     */
    public function bulkActivate(BillerBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update billers')) {
            return response()->forbidden('Permission denied for bulk update billers.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} billers activated"
        );
    }

    /**
     * Bulk Deactivate Billers
     *
     * Set the active status of multiple billers to false.
     */
    public function bulkDeactivate(BillerBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update billers')) {
            return response()->forbidden('Permission denied for bulk update billers.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} billers deactivated"
        );
    }

    /**
     * Import Billers
     *
     * Import multiple billers into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import billers')) {
            return response()->forbidden('Permission denied for import billers.');
        }

        $this->service->importBillers($request->file('file'));

        return response()->success(null, 'Billers imported successfully');
    }

    /**
     * Export Billers
     *
     * Export a list of billers to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export billers')) {
            return response()->forbidden('Permission denied for export billers.');
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

            if (!$user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (!$mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'billers_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Biller Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: ' . $user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import billers')) {
            return response()->forbidden('Permission denied for downloading billers import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

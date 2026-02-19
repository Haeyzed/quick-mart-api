<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Brands\BrandBulkActionRequest;
use App\Http\Requests\Brands\StoreBrandRequest;
use App\Http\Requests\Brands\UpdateBrandRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\BrandResource;
use App\Mail\ExportMail;
use App\Models\Brand;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\BrandService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class BrandController
 *
 * API Controller for Brand CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to BrandService.
 *
 * @tags Brand Management
 */
class BrandController extends Controller
{
    /**
     * BrandController constructor.
     */
    public function __construct(
        private readonly BrandService $service
    )
    {
    }

    /**
     * List Brands
     *
     * Display a paginated listing of brands. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view brands')) {
            return response()->forbidden('Permission denied for viewing brands list.');
        }

        $brands = $this->service->getPaginatedBrands(
            $request->validate([
                /**
                 * Search term to filter brands by name or slug.
                 * @example "Apple"
                 */
                'search'     => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 * @example true
                 */
                'is_active'  => ['nullable', 'boolean'],
                /**
                 * Filter brands starting from this date.
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter brands up to this date.
                 * @example "2024-12-31"
                 */
                'end_date'   => ['nullable', 'date', 'after_or_equal:start_date'],
            ]),
            /**
             * Amount of items per page.
             * @example 50
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            BrandResource::collection($brands),
            'Brands retrieved successfully'
        );
    }

    /**
     * Get Brand Options
     *
     * Retrieve a simplified list of active brands for use in dropdowns or select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view brands')) {
            return response()->forbidden('Permission denied for viewing brands options.');
        }

        return response()->success($this->service->getOptions(), 'Brand options retrieved successfully');
    }

    /**
     * Create Brand
     *
     * Store a newly created brand in the system.
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create brands')) {
            return response()->forbidden('Permission denied for create brand.');
        }

        $brand = $this->service->createBrand($request->validated());

        return response()->success(
            new BrandResource($brand),
            'Brand created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Brand
     *
     * Retrieve the details of a specific brand by its ID.
     */
    public function show(Brand $brand): JsonResponse
    {
        if (auth()->user()->denies('view brand details')) {
            return response()->forbidden('Permission denied for view brand.');
        }

        return response()->success(
            new BrandResource($brand),
            'Brand details retrieved successfully'
        );
    }

    /**
     * Update Brand
     *
     * Update the specified brand's information.
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        if (auth()->user()->denies('update brands')) {
            return response()->forbidden('Permission denied for update brand.');
        }

        $updatedBrand = $this->service->updateBrand($brand, $request->validated());

        return response()->success(
            new BrandResource($updatedBrand),
            'Brand updated successfully'
        );
    }

    /**
     * Delete Brand
     *
     * Remove the specified brand from storage.
     */
    public function destroy(Brand $brand): JsonResponse
    {
        if (auth()->user()->denies('delete brands')) {
            return response()->forbidden('Permission denied for delete brand.');
        }

        $this->service->deleteBrand($brand);

        return response()->success(null, 'Brand deleted successfully');
    }

    /**
     * Bulk Delete Brands
     *
     * Delete multiple brands simultaneously using an array of IDs.
     */
    public function bulkDestroy(BrandBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete brands')) {
            return response()->forbidden('Permission denied for bulk delete brands.');
        }

        $count = $this->service->bulkDeleteBrands($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} brands"
        );
    }

    /**
     * Bulk Activate Brands
     *
     * Set the active status of multiple brands to true.
     */
    public function bulkActivate(BrandBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update brands')) {
            return response()->forbidden('Permission denied for bulk update brands.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} brands activated"
        );
    }

    /**
     * Bulk Deactivate Brands
     *
     * Set the active status of multiple brands to false.
     */
    public function bulkDeactivate(BrandBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update brands')) {
            return response()->forbidden('Permission denied for bulk update brands.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} brands deactivated"
        );
    }

    /**
     * Import Brands
     *
     * Import multiple brands into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import brands')) {
            return response()->forbidden('Permission denied for import brands.');
        }

        $this->service->importBrands($request->file('file'));

        return response()->success(null, 'Brands imported successfully');
    }

    /**
     * Export Brands
     *
     * Export a list of brands to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns. Delivery methods include direct download or email.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export brands')) {
            return response()->forbidden('Permission denied for export brands.');
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

        // 3. Handle Email Method
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
                    'brands_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Brand Export Is Ready',
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
        if (auth()->user()->denies('import brands')) {
            return response()->forbidden('Permission denied for downloading brands import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

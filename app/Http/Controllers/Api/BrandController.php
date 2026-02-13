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
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests; // 1. Import this trait
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class BrandController
 *
 * API Controller for Brand CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to BrandService.
 *
 * @group Brand Management
 */
class BrandController extends Controller
{
    use AuthorizesRequests; // 2. Use the trait here

    /**
     * BrandController constructor.
     *
     * @param BrandService $service
     */
    public function __construct(
        private readonly BrandService $service
    ) {
        // Standard Resource Authorization (index, show, store, update, destroy)
        $this->authorizeResource(Brand::class, 'brand');
    }

    /**
     * Display a paginated listing of brands.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $brands = $this->service->getPaginatedBrands(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        return response()->success(
            BrandResource::collection($brands),
            'Brands retrieved successfully'
        );
    }

    /**
     * Store a newly created brand.
     *
     * @param StoreBrandRequest $request
     * @return JsonResponse
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $brand = $this->service->createBrand($request->validated());

        return response()->success(
            new BrandResource($brand),
            'Brand created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified brand.
     *
     * @param Brand $brand
     * @return JsonResponse
     */
    public function show(Brand $brand): JsonResponse
    {
        return response()->success(
            new BrandResource($brand),
            'Brand details retrieved successfully'
        );
    }

    /**
     * Update the specified brand.
     *
     * @param UpdateBrandRequest $request
     * @param Brand $brand
     * @return JsonResponse
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        $updatedBrand = $this->service->updateBrand($brand, $request->validated());

        return response()->success(
            new BrandResource($updatedBrand),
            'Brand updated successfully'
        );
    }

    /**
     * Remove the specified brand (soft delete).
     *
     * @param Brand $brand
     * @return JsonResponse
     */
    public function destroy(Brand $brand): JsonResponse
    {
        $this->service->deleteBrand($brand);

        return response()->success(null, 'Brand deleted successfully');
    }

    /**
     * Bulk delete brands.
     *
     * @param BrandBulkActionRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(BrandBulkActionRequest $request): JsonResponse
    {
        Gate::authorize('deleteAny', Brand::class);

        $count = $this->service->bulkDeleteBrands($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} brands"
        );
    }

    /**
     * Bulk activate brands.
     *
     * @param BrandBulkActionRequest $request
     * @return JsonResponse
     */
    public function bulkActivate(BrandBulkActionRequest $request): JsonResponse
    {
        Gate::authorize('updateAny', Brand::class);

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} brands activated"
        );
    }

    /**
     * Bulk deactivate brands.
     *
     * @param BrandBulkActionRequest $request
     * @return JsonResponse
     */
    public function bulkDeactivate(BrandBulkActionRequest $request): JsonResponse
    {
        Gate::authorize('updateAny', Brand::class);

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} brands deactivated"
        );
    }

    /**
     * Import brands from Excel/CSV.
     *
     * @param ImportRequest $request
     * @return JsonResponse
     */
    public function import(ImportRequest $request): JsonResponse
    {
        Gate::authorize('import', Brand::class);

        $this->service->importBrands($request->file('file'));

        return response()->success(null, 'Brands imported successfully');
    }

    /**
     * Export brands to Excel or PDF.
     *
     * @param ExportRequest $request
     * @return JsonResponse|BinaryFileResponse
     * @throws AuthorizationException
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        Gate::authorize('export', Brand::class);

        $validated = $request->validated();

        // 1. Generate the file via service
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? []
        );

        // 2. Handle Download Method
        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

        // 3. Handle Email Method
        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (! $user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found for email delivery.',
                ], 404);
            }

            $mailSetting = MailSetting::default()->first();

            if (! $mailSetting) {
                return response()->json([
                    'success' => false,
                    'message' => 'System mail settings are not configured. Cannot send email.',
                ], 500);
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

        return response()->badRequest(
            'Invalid export method provided.',
        );
    }
}
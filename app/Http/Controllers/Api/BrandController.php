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
use Illuminate\Http\Response;
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
    /**
     * BrandController constructor.
     */
    public function __construct(
        private readonly BrandService $service
    ) {}

    /**
     * Display a paginated listing of brands.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view brands')) {
            return response()->forbidden('Permission denied for viewing brands list.');
        }

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
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified brand.
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
     * Update the specified brand.
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
     * Remove the specified brand (soft delete).
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
     * Bulk delete brands.
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
     * Bulk activate brands.
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
     * Bulk deactivate brands.
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
     * Import brands from Excel/CSV.
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
     * Export brands to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export brands')) {
            return response()->forbidden('Permission denied for export brands.');
        }

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
                    'brands_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Brand Export Is Ready',
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
}

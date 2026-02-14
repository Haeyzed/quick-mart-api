<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Billers\BillerBulkDestroyRequest;
use App\Http\Requests\Billers\BillerBulkUpdateRequest;
use App\Http\Requests\Billers\BillerIndexRequest;
use App\Http\Requests\Billers\BillerRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\BillerResource;
use App\Models\Biller;
use App\Models\User;
use App\Services\BillerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Biller CRUD and bulk operations.
 *
 * Handles index, store, show, update, destroy, bulk activate/deactivate/destroy,
 * import, export, and getAllActive. All responses use the ResponseServiceProvider macros.
 *
 * @group Biller Management
 */
class BillerController extends Controller
{
    public function __construct(
        private readonly BillerService $service
    )
    {
    }

    /**
     * Display a paginated listing of billers.
     *
     * @param BillerIndexRequest $request Validated query params: per_page, page, status, search.
     * @return JsonResponse Paginated billers with meta and links.
     */
    public function index(BillerIndexRequest $request): JsonResponse
    {
        $billers = $this->service->getBillers(
            $request->validated(),
            (int)$request->input('per_page', 10)
        );

        $billers->through(fn(Biller $biller) => new BillerResource($biller));

        return response()->success($billers, 'Billers fetched successfully');
    }

    /**
     * Store a newly created biller.
     *
     * @param BillerRequest $request Validated biller attributes.
     * @return JsonResponse Created biller with 201 status.
     */
    public function store(BillerRequest $request): JsonResponse
    {
        $biller = $this->service->createBiller($request->validated());

        return response()->success(
            new BillerResource($biller),
            'Biller created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified biller.
     *
     * @param Biller $biller The biller instance resolved via route model binding.
     */
    public function show(Biller $biller): JsonResponse
    {
        $biller = $this->service->getBiller($biller);

        return response()->success(new BillerResource($biller), 'Biller retrieved successfully');
    }

    /**
     * Update the specified biller.
     *
     * @param BillerRequest $request Validated biller attributes.
     * @param Biller $biller The biller instance to update.
     * @return JsonResponse Updated biller.
     */
    public function update(BillerRequest $request, Biller $biller): JsonResponse
    {
        $biller = $this->service->updateBiller($biller, $request->validated());

        return response()->success(new BillerResource($biller), 'Biller updated successfully');
    }

    /**
     * Remove the specified biller (deactivates it).
     *
     * @param Biller $biller The biller instance to delete.
     * @return JsonResponse Success message.
     */
    public function destroy(Biller $biller): JsonResponse
    {
        $this->service->deleteBiller($biller);

        return response()->success(null, 'Biller deleted successfully');
    }

    /**
     * Get all active billers (for dropdowns).
     *
     * @return JsonResponse Collection of active billers.
     */
    public function getAllActive(): JsonResponse
    {
        $billers = $this->service->getAllActive();

        return response()->success(BillerResource::collection($billers), 'Active billers fetched successfully');
    }

    /**
     * Bulk delete billers (deactivates them).
     *
     * @param BillerBulkDestroyRequest $request Validated ids array.
     * @return JsonResponse Deleted count and message.
     */
    public function bulkDestroy(BillerBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteBillers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} billers"
        );
    }

    /**
     * Bulk activate billers by ID.
     *
     * @param BillerBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Activated count and message.
     */
    public function bulkActivate(BillerBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateBillers($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} billers activated");
    }

    /**
     * Bulk deactivate billers by ID.
     *
     * @param BillerBulkUpdateRequest $request Validated ids array.
     * @return JsonResponse Deactivated count and message.
     */
    public function bulkDeactivate(BillerBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateBillers($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} billers deactivated");
    }

    /**
     * Import billers from Excel/CSV file.
     *
     * @param ImportRequest $request Validated file upload.
     * @return JsonResponse Success message.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importBillers($request->file('file'));

        return response()->success(null, 'Billers imported successfully');
    }

    /**
     * Export billers to Excel or PDF.
     *
     * Supports download or email delivery based on method.
     *
     * @param ExportRequest $request Validated export params: ids, format, method, columns, user_id (if email).
     * @return JsonResponse|BinaryFileResponse Success message or file download.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();
        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportBillers(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return response()->download(Storage::disk('public')->path($filePath));
        }

        return response()->success(null, 'Export processed and sent via email');
    }
}

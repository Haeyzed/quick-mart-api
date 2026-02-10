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
 * API Controller for Biller CRUD.
 *
 * @group Biller Management
 */
class BillerController extends Controller
{
    public function __construct(
        private readonly BillerService $service
    ) {}

    public function index(BillerIndexRequest $request): JsonResponse
    {
        $billers = $this->service->getBillers(
            $request->validated(),
            (int) $request->input('per_page', 10)
        );

        $billers->through(fn (Biller $biller) => new BillerResource($biller));

        return response()->success($billers, 'Billers fetched successfully');
    }

    public function store(BillerRequest $request): JsonResponse
    {
        $biller = $this->service->createBiller($request->validated());

        return response()->success(
            new BillerResource($biller),
            'Biller created successfully',
            Response::HTTP_CREATED
        );
    }

    public function show(Biller $biller): JsonResponse
    {
        $biller = $this->service->getBiller($biller);

        return response()->success(new BillerResource($biller), 'Biller retrieved successfully');
    }

    public function update(BillerRequest $request, Biller $biller): JsonResponse
    {
        $biller = $this->service->updateBiller($biller, $request->validated());

        return response()->success(new BillerResource($biller), 'Biller updated successfully');
    }

    public function destroy(Biller $biller): JsonResponse
    {
        $this->service->deleteBiller($biller);

        return response()->success(null, 'Biller deleted successfully');
    }

    /**
     * Get all active billers (for dropdowns).
     */
    public function getAllActive(): JsonResponse
    {
        $billers = $this->service->getAllActive();

        return response()->success(BillerResource::collection($billers), 'Active billers fetched successfully');
    }

    public function bulkDestroy(BillerBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteBillers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} billers"
        );
    }

    public function bulkActivate(BillerBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkActivateBillers($request->validated()['ids']);

        return response()->success(['activated_count' => $count], "{$count} billers activated");
    }

    public function bulkDeactivate(BillerBulkUpdateRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeactivateBillers($request->validated()['ids']);

        return response()->success(['deactivated_count' => $count], "{$count} billers deactivated");
    }

    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importBillers($request->file('file'));

        return response()->success(null, 'Billers imported successfully');
    }

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

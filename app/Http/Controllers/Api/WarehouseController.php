<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Warehouses\StoreWarehouseRequest;
use App\Http\Requests\Warehouses\UpdateWarehouseRequest;
use App\Http\Requests\Warehouses\WarehouseBulkActionRequest;
use App\Http\Resources\WarehouseResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\WarehouseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class WarehouseController
 *
 * API Controller for Warehouse CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to WarehouseService.
 *
 * @group Warehouse Management
 */
class WarehouseController extends Controller
{
    /**
     * WarehouseController constructor.
     */
    public function __construct(
        private readonly WarehouseService $service
    )
    {
    }

    /**
     * Display a paginated listing of warehouses.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view warehouses')) {
            return response()->forbidden('Permission denied for viewing warehouses list.');
        }

        $warehouses = $this->service->getPaginatedWarehouses(
            $request->all(),
            (int)$request->input('per_page', 10)
        );

        return response()->success(
            WarehouseResource::collection($warehouses),
            'Warehouses retrieved successfully'
        );
    }

    /**
     * Get warehouse options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view warehouses')) {
            return response()->forbidden('Permission denied for viewing warehouses options.');
        }

        return response()->success($this->service->getOptions(), 'Warehouse options retrieved successfully');
    }

    /**
     * Store a newly created warehouse.
     */
    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create warehouses')) {
            return response()->forbidden('Permission denied for create warehouse.');
        }

        $warehouse = $this->service->createWarehouse($request->validated());

        return response()->success(
            new WarehouseResource($warehouse),
            'Warehouse created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified warehouse.
     */
    public function show(Warehouse $warehouse): JsonResponse
    {
        if (auth()->user()->denies('view warehouse details')) {
            return response()->forbidden('Permission denied for view warehouse.');
        }

        return response()->success(
            new WarehouseResource($warehouse),
            'Warehouse details retrieved successfully'
        );
    }

    /**
     * Update the specified warehouse.
     */
    public function update(UpdateWarehouseRequest $request, Warehouse $warehouse): JsonResponse
    {
        if (auth()->user()->denies('update warehouses')) {
            return response()->forbidden('Permission denied for update warehouse.');
        }

        $updatedWarehouse = $this->service->updateWarehouse($warehouse, $request->validated());

        return response()->success(
            new WarehouseResource($updatedWarehouse),
            'Warehouse updated successfully'
        );
    }

    /**
     * Remove the specified warehouse (soft delete).
     */
    public function destroy(Warehouse $warehouse): JsonResponse
    {
        if (auth()->user()->denies('delete warehouses')) {
            return response()->forbidden('Permission denied for delete warehouse.');
        }

        $this->service->deleteWarehouse($warehouse);

        return response()->success(null, 'Warehouse deleted successfully');
    }

    /**
     * Bulk delete warehouses.
     */
    public function bulkDestroy(WarehouseBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete warehouses')) {
            return response()->forbidden('Permission denied for bulk delete warehouses.');
        }

        $count = $this->service->bulkDeleteWarehouses($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} warehouses"
        );
    }

    /**
     * Bulk activate warehouses.
     */
    public function bulkActivate(WarehouseBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update warehouses')) {
            return response()->forbidden('Permission denied for bulk update warehouses.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} warehouses activated"
        );
    }

    /**
     * Bulk deactivate warehouses.
     */
    public function bulkDeactivate(WarehouseBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update warehouses')) {
            return response()->forbidden('Permission denied for bulk update warehouses.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} warehouses deactivated"
        );
    }

    /**
     * Import warehouses from Excel/CSV.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import warehouses')) {
            return response()->forbidden('Permission denied for import warehouses.');
        }

        $this->service->importWarehouses($request->file('file'));

        return response()->success(null, 'Warehouses imported successfully');
    }

    /**
     * Export warehouses to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export warehouses')) {
            return response()->forbidden('Permission denied for export warehouses.');
        }

        $validated = $request->validated();

        // 1. Generate the file via service
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
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
                    'warehouses_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Warehouse Export Is Ready',
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
     * Download warehouses module import sample template.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import warehouses')) {
            return response()->forbidden('Permission denied for downloading warehouses import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

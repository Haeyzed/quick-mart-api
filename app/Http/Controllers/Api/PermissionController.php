<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Permissions\PermissionBulkActionRequest;
use App\Http\Requests\Permissions\StorePermissionRequest;
use App\Http\Requests\Permissions\UpdatePermissionRequest;
use App\Http\Resources\PermissionResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Permission;
use App\Models\User;
use App\Services\PermissionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class PermissionController
 *
 * API Controller for Permission CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to PermissionService.
 *
 * @group Permission Management
 */
class PermissionController extends Controller
{
    /**
     * PermissionController constructor.
     */
    public function __construct(
        private readonly PermissionService $service
    )
    {
    }

    /**
     * Display a paginated listing of permissions.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view permissions')) {
            return response()->forbidden('Permission denied for viewing permissions list.');
        }

        $permissions = $this->service->getPaginatedPermissions(
            $request->all(),
            (int)$request->input('per_page', 10)
        );

        return response()->success(
            PermissionResource::collection($permissions),
            'Permissions retrieved successfully'
        );
    }

    /**
     * Get permission options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view permissions')) {
            return response()->forbidden('Permission denied for viewing permissions options.');
        }

        return response()->success($this->service->getOptions(), 'Permission options retrieved successfully');
    }

    /**
     * Store a newly created permission.
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create permissions')) {
            return response()->forbidden('Permission denied for create permission.');
        }

        $permission = $this->service->createPermission($request->validated());

        return response()->success(
            new PermissionResource($permission),
            'Permission created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified permission.
     */
    public function show(Permission $permission): JsonResponse
    {
        if (auth()->user()->denies('view permission details')) {
            return response()->forbidden('Permission denied for view permission.');
        }

        return response()->success(
            new PermissionResource($permission),
            'Permission details retrieved successfully'
        );
    }

    /**
     * Update the specified permission.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        if (auth()->user()->denies('update permissions')) {
            return response()->forbidden('Permission denied for update permission.');
        }

        $updatedPermission = $this->service->updatePermission($permission, $request->validated());

        return response()->success(
            new PermissionResource($updatedPermission),
            'Permission updated successfully'
        );
    }

    /**
     * Remove the specified permission.
     */
    public function destroy(Permission $permission): JsonResponse
    {
        if (auth()->user()->denies('delete permissions')) {
            return response()->forbidden('Permission denied for delete permission.');
        }

        $this->service->deletePermission($permission);

        return response()->success(null, 'Permission deleted successfully');
    }

    /**
     * Bulk delete permissions.
     */
    public function bulkDestroy(PermissionBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete permissions')) {
            return response()->forbidden('Permission denied for bulk delete permissions.');
        }

        $count = $this->service->bulkDeletePermissions($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} permissions"
        );
    }

    /**
     * Bulk activate permissions.
     */
    public function bulkActivate(PermissionBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update permissions')) {
            return response()->forbidden('Permission denied for bulk update permissions.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} permissions activated"
        );
    }

    /**
     * Bulk deactivate permissions.
     */
    public function bulkDeactivate(PermissionBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update permissions')) {
            return response()->forbidden('Permission denied for bulk update permissions.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} permissions deactivated"
        );
    }

    /**
     * Import permissions from Excel/CSV.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import permissions')) {
            return response()->forbidden('Permission denied for import permissions.');
        }
        $this->service->importPermissions($request->file('file'));
        return response()->success(null, 'Permissions imported successfully');
    }

    /**
     * Export permissions to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export permissions')) {
            return response()->forbidden('Permission denied for export permissions.');
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
                    'permissions_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Permissions Export Is Ready',
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
     * Download permissions import sample template.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import permissions')) {
            return response()->forbidden('Permission denied for downloading permissions import template.');
        }
        $path = $this->service->download();
        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

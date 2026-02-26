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
 * @tags Permission Management
 */
class PermissionController extends Controller
{
    public function __construct(
        private readonly PermissionService $service
    ) {}

    /**
     * List Permissions
     *
     * Display a paginated listing of permissions.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view permissions')) {
            return response()->forbidden('Permission denied for viewing permissions list.');
        }

        $permissions = $this->service->getPaginatedPermissions(
            $request->validate([
                /**
                 * Search term to filter shifts by name.
                 *
                 * @example "Morning"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter shifts starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter shifts up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
                /**
                 * Filter by guard name.
                 *
                 * @example "web"
                 */
                'guard_name' => ['nullable', 'string'],
                /**
                 * Filter by module.
                 *
                 * @example "hrm"
                 */
                'module' => ['nullable', 'string'],
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
            PermissionResource::collection($permissions),
            'Permissions retrieved successfully'
        );
    }

    /**
     * List Options
     *
     * Retrieves a lightweight list of permissions for dropdowns.
     */
    public function options(): JsonResponse
    {
        return response()->success(
            $this->service->getOptions(),
            'Permission options retrieved successfully'
        );
    }

    /**
     * Create Permission
     */
    public function store(StorePermissionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create permissions')) {
            return response()->forbidden('Permission denied for creating permission.');
        }

        $permission = $this->service->createPermission($request->validated());

        return response()->success(
            new PermissionResource($permission),
            'Permission created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Permission
     */
    public function show(Permission $permission): JsonResponse
    {
        if (auth()->user()->denies('view permissions')) {
            return response()->forbidden('Permission denied for viewing permission.');
        }

        return response()->success(
            new PermissionResource($permission),
            'Permission retrieved successfully'
        );
    }

    /**
     * Update Permission
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): JsonResponse
    {
        if (auth()->user()->denies('update permissions')) {
            return response()->forbidden('Permission denied for updating permission.');
        }

        $updatedPermission = $this->service->updatePermission($permission, $request->validated());

        return response()->success(
            new PermissionResource($updatedPermission),
            'Permission updated successfully'
        );
    }

    /**
     * Delete Permission
     */
    public function destroy(Permission $permission): JsonResponse
    {
        if (auth()->user()->denies('delete permissions')) {
            return response()->forbidden('Permission denied for deleting permission.');
        }

        $this->service->deletePermission($permission);

        return response()->success(null, 'Permission deleted successfully');
    }

    /**
     * Bulk Delete Permissions
     */
    public function bulkDestroy(PermissionBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete permissions')) {
            return response()->forbidden('Permission denied for bulk deleting permissions.');
        }

        $count = $this->service->bulkDeletePermissions($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} permissions"
        );
    }

    /**
     * Bulk Mark Active/Inactive
     */
    public function bulkUpdateStatus(PermissionBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update permissions')) {
            return response()->forbidden('Permission denied for updating permissions.');
        }

        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);
        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], $isActive);
        $statusText = $isActive ? 'active' : 'inactive';

        return response()->success(
            ['updated_count' => $count],
            "Successfully marked {$count} permissions as {$statusText}"
        );
    }

    /**
     * Import Permissions
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import permissions')) {
            return response()->forbidden('Permission denied for importing permissions.');
        }

        $this->service->importPermissions($request->file('file'));

        return response()->success(null, 'Permissions imported successfully');
    }

    /**
     * Export Permissions
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export permissions')) {
            return response()->forbidden('Permission denied for exporting permissions.');
        }

        $validated = $request->validated();
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            $validated['filters'] ?? []
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()->download(Storage::disk('public')->path($path))->deleteFileAfterSend();
        }

        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::find($userId);

            if (!$user || !$user->email) {
                return response()->error('User not found or missing email for delivery.');
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
                    'permissions_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Permissions Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(null, 'Export is being processed and will be sent to email: '.$user->email);
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download Permission Import Template
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

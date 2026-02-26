<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Roles\RoleBulkActionRequest;
use App\Http\Requests\Roles\StoreRoleRequest;
use App\Http\Requests\Roles\UpdateRoleRequest;
use App\Http\Resources\RoleResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Role;
use App\Models\User;
use App\Services\RoleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class RoleController
 *
 * API Controller for Role CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to RoleService.
 *
 * @tags Role Management
 */
class RoleController extends Controller
{
    public function __construct(
        private readonly RoleService $service
    ) {}

    /**
     * List Roles
     *
     * Display a paginated listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view roles')) {
            return response()->forbidden('Permission denied for viewing roles list.');
        }

        $roles = $this->service->getPaginatedRoles(
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
            RoleResource::collection($roles),
            'Roles retrieved successfully'
        );
    }

    /**
     * List Options
     *
     * Retrieves a lightweight list of roles for dropdowns.
     */
    public function options(): JsonResponse
    {
        return response()->success(
            $this->service->getOptions(),
            'Role options retrieved successfully'
        );
    }

    /**
     * Create Role
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create roles')) {
            return response()->forbidden('Permission denied for creating role.');
        }

        $role = $this->service->createRole($request->validated());

        return response()->success(
            new RoleResource($role),
            'Role created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Role
     */
    public function show(Role $role): JsonResponse
    {
        if (auth()->user()->denies('view roles')) {
            return response()->forbidden('Permission denied for viewing role.');
        }

        return response()->success(
            new RoleResource($role->load('permissions')),
            'Role retrieved successfully'
        );
    }

    /**
     * Update Role
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        if (auth()->user()->denies('update roles')) {
            return response()->forbidden('Permission denied for updating role.');
        }

        $updatedRole = $this->service->updateRole($role, $request->validated());

        return response()->success(
            new RoleResource($updatedRole),
            'Role updated successfully'
        );
    }

    /**
     * Delete Role
     */
    public function destroy(Role $role): JsonResponse
    {
        if (auth()->user()->denies('delete roles')) {
            return response()->forbidden('Permission denied for deleting role.');
        }

        if (in_array($role->name, ['admin', 'super-admin', 'Customer'])) {
            return response()->error("System roles cannot be deleted.", ResponseAlias::HTTP_FORBIDDEN);
        }

        $this->service->deleteRole($role);

        return response()->success(null, 'Role deleted successfully');
    }

    /**
     * Bulk Delete Roles
     */
    public function bulkDestroy(RoleBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete roles')) {
            return response()->forbidden('Permission denied for bulk deleting roles.');
        }

        $count = $this->service->bulkDeleteRoles($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} roles"
        );
    }

    /**
     * Bulk Mark Active/Inactive
     */
    public function bulkUpdateStatus(RoleBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update roles')) {
            return response()->forbidden('Permission denied for updating roles.');
        }

        $isActive = filter_var($request->input('is_active', true), FILTER_VALIDATE_BOOLEAN);
        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], $isActive);
        $statusText = $isActive ? 'active' : 'inactive';

        return response()->success(
            ['updated_count' => $count],
            "Successfully marked {$count} roles as {$statusText}"
        );
    }

    /**
     * Import Roles
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import roles')) {
            return response()->forbidden('Permission denied for importing roles.');
        }

        $this->service->importRoles($request->file('file'));

        return response()->success(null, 'Roles imported successfully');
    }

    /**
     * Export Roles
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export roles')) {
            return response()->forbidden('Permission denied for exporting roles.');
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
                    'roles_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Roles Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(null, 'Export is being processed and will be sent to email: '.$user->email);
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download Role Import Template
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import roles')) {
            return response()->forbidden('Permission denied for downloading roles import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

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
 * @group Role Management
 */
class RoleController extends Controller
{
    /**
     * RoleController constructor.
     */
    public function __construct(
        private readonly RoleService $service
    )
    {
    }

    /**
     * Display a paginated listing of roles.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view roles')) {
            return response()->forbidden('Permission denied for viewing roles list.');
        }

        $roles = $this->service->getPaginatedRoles(
            $request->all(),
            (int)$request->input('per_page', 10)
        );

        return response()->success(
            RoleResource::collection($roles),
            'Roles retrieved successfully'
        );
    }

    /**
     * Get role options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view roles')) {
            return response()->forbidden('Permission denied for viewing roles options.');
        }

        return response()->success($this->service->getOptions(), 'Role options retrieved successfully');
    }

    /**
     * Store a newly created role.
     */
    public function store(StoreRoleRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create roles')) {
            return response()->forbidden('Permission denied for create role.');
        }

        $role = $this->service->createRole($request->validated());

        return response()->success(
            new RoleResource($role),
            'Role created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified role.
     */
    public function show(Role $role): JsonResponse
    {
        if (auth()->user()->denies('view role details')) {
            return response()->forbidden('Permission denied for view role.');
        }

        return response()->success(
            new RoleResource($role->load('permissions')),
            'Role details retrieved successfully'
        );
    }

    /**
     * Update the specified role.
     */
    public function update(UpdateRoleRequest $request, Role $role): JsonResponse
    {
        if (auth()->user()->denies('update roles')) {
            return response()->forbidden('Permission denied for update role.');
        }

        $updatedRole = $this->service->updateRole($role, $request->validated());

        return response()->success(
            new RoleResource($updatedRole),
            'Role updated successfully'
        );
    }

    /**
     * Remove the specified role.
     */
    public function destroy(Role $role): JsonResponse
    {
        if (auth()->user()->denies('delete roles')) {
            return response()->forbidden('Permission denied for delete role.');
        }

        $this->service->deleteRole($role);

        return response()->success(null, 'Role deleted successfully');
    }

    /**
     * Bulk delete roles.
     */
    public function bulkDestroy(RoleBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete roles')) {
            return response()->forbidden('Permission denied for bulk delete roles.');
        }

        $count = $this->service->bulkDeleteRoles($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} roles"
        );
    }

    /**
     * Bulk activate roles.
     */
    public function bulkActivate(RoleBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update roles')) {
            return response()->forbidden('Permission denied for bulk update roles.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} roles activated"
        );
    }

    /**
     * Bulk deactivate roles.
     */
    public function bulkDeactivate(RoleBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update roles')) {
            return response()->forbidden('Permission denied for bulk update roles.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} roles deactivated"
        );
    }

    /**
     * Import roles from Excel/CSV.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import roles')) {
            return response()->forbidden('Permission denied for import roles.');
        }
        $this->service->importRoles($request->file('file'));
        return response()->success(null, 'Roles imported successfully');
    }

    /**
     * Export roles to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export roles')) {
            return response()->forbidden('Permission denied for export roles.');
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
                    'roles_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Roles Export Is Ready',
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
     * Download roles import sample template.
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

<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\UsersExport;
use App\Imports\UsersImport;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Class UserService
 *
 * Handles all core business logic and database interactions for Users.
 * Delegates role and permission assignments to the UserRolePermissionService.
 * Supports pagination, importing, exporting, and bulk operations.
 */
class UserService extends BaseService
{
    /**
     * The application path where user images are stored.
     */
    private const IMAGE_PATH = 'images/users';

    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * UserService constructor.
     *
     * @param UserRolePermissionService $userRolePermissionService
     */
    public function __construct(
        private readonly UserRolePermissionService $userRolePermissionService
    ) {}

    /**
     * Get paginated users based on filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = User::query()
            ->with(['roles', 'permissions', 'biller', 'warehouse'])
            ->filter($filters)
            ->latest();

        return $query->paginate($perPage);
    }

    /**
     * Get User Options for Dropdowns
     *
     * Retrieve a simplified list of active users.
     *
     * @param int|null $warehouseId
     * @return SupportCollection
     */
    public function getOptions(?int $warehouseId = null): SupportCollection
    {
        return User::active()
            ->select('id', 'name')
            ->when($warehouseId, fn($q) => $q->where('warehouse_id', $warehouseId))
            ->orderBy('name')
            ->get()
            ->map(fn(User $user) => [
                'value' => $user->id,
                'label' => $user->name,
            ]);
    }

    /**
     * Retrieve a single user instance with eager-loaded roles and permissions.
     *
     * @param User $user
     * @return User
     */
    public function getUser(User $user): User
    {
        return $user->fresh(['roles', 'permissions', 'biller', 'warehouse']);
    }

    /**
     * Create a newly registered user, handle their image upload, and assign roles/permissions.
     * Automatically handles password hashing.
     *
     * @param array<string, mixed> $data
     * @return User
     */
    public function create(array $data): User
    {
        return DB::transaction(function () use ($data) {
            // Handle standalone User image uploads
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
                $data['image_path'] = $path;
                $data['image_url'] = $this->uploadService->url($path);
                unset($data['image']);
            }

            // Hash the password automatically if provided
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $user = User::create($data);

            // Sync Roles and Permissions
            if (isset($data['roles']) || isset($data['permissions'])) {
                $this->userRolePermissionService->assignRolesAndPermissions(
                    $user,
                    $data['roles'] ?? [],
                    $data['permissions'] ?? []
                );
            }

            return $user->fresh(['roles', 'permissions']);
        });
    }

    /**
     * Update an existing user, process replacement uploads, and sync their roles/permissions.
     * Automatically hashes a new password if one is provided.
     *
     * @param User $user
     * @param array<string, mixed> $data
     * @return User
     */
    public function update(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($user->image_path) {
                    $this->uploadService->delete($user->image_path);
                }
                $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
                $data['image_path'] = $path;
                $data['image_url'] = $this->uploadService->url($path);
                unset($data['image']);
            }

            // Hash the password if explicitly provided, otherwise remove it from payload
            if (!empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $user->update($data);

            // Sync updated Roles and Permissions
            if (isset($data['roles']) || isset($data['permissions'])) {
                $this->userRolePermissionService->assignRolesAndPermissions(
                    $user,
                    $data['roles'] ?? [],
                    $data['permissions'] ?? []
                );
            }

            return $user->fresh(['roles', 'permissions']);
        });
    }

    /**
     * Soft delete a user from the system.
     *
     * @param User $user
     * @return void
     */
    public function delete(User $user): void
    {
        DB::transaction(fn () => $user->delete());
    }

    /**
     * Import multiple users from an uploaded file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function import(UploadedFile $file): void
    {
        ExcelFacade::import(new UsersImport, $file);
    }

    /**
     * Download a users CSV template.
     *
     * @return string
     * @throws RuntimeException
     */
    public function download(): string
    {
        $fileName = 'users-sample.csv';
        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException('Template users not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing user data.
     *
     * @param array<int> $ids
     * @param string $format
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters
     * @return string
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'users_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new UsersExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }

    /**
     * Assign specific roles and direct permissions to a user.
     *
     * @param User $user
     * @param array<int|string|Role>|null $roles
     * @param array<int|string|Permission>|null $directPermissions
     * @return void
     */
    public function assignRolesAndPermissions(
        User $user,
        ?array $roles = null,
        ?array $directPermissions = null
    ): void {
        $this->userRolePermissionService->assignRolesAndPermissions($user, $roles, $directPermissions);
    }

    /**
     * Sync user permissions (collects from roles and merges with direct permissions).
     *
     * @param User $user
     * @param array<int|string|Permission>|null $directPermissions Optional direct permissions to add
     * @return void
     */
    public function syncUserPermissions(User $user, ?array $directPermissions = null): void
    {
        $this->userRolePermissionService->syncUserPermissions($user, $directPermissions);
    }

    /**
     * Get all aggregated permissions for a user.
     *
     * @param User $user
     * @return SupportCollection<int, Permission>
     */
    public function getUserPermissions(User $user): SupportCollection
    {
        return $this->userRolePermissionService->getUserPermissions($user);
    }

    /**
     * Get all roles assigned to a user.
     *
     * @param User $user
     * @return Collection<int, Role>
     */
    public function getUserRoles(User $user): Collection
    {
        return $this->userRolePermissionService->getUserRoles($user);
    }

    /**
     * Check if a user has a specific permission.
     *
     * @param User $user
     * @param string $permission Permission name
     * @return bool
     */
    public function checkPermission(User $user, string $permission): bool
    {
        return $this->userRolePermissionService->checkPermission($user, $permission);
    }
}

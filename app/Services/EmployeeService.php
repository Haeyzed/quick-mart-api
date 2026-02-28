<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class EmployeeService
 *
 * Handles all core business logic and database interactions for Employees.
 * Acts as the intermediary between the controllers and the database layer.
 */
class EmployeeService
{
    /**
     * The application path where employee images are stored.
     */
    private const IMAGE_PATH = 'images/employees';

    /**
     * The application path where import template files are stored.
     */
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * EmployeeService constructor.
     *
     * @param  UploadService  $uploadService  Service responsible for handling file uploads.
     * @param  UserRolePermissionService  $userRolePermissionService  Service responsible for syncing roles and permissions.
     */
    public function __construct(
        private readonly UploadService $uploadService,
        private readonly UserRolePermissionService $userRolePermissionService
    ) {}

    /**
     * Get paginated employees based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedEmployees(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Employee::query()
            ->with([
                'department:id,name',
                'designation:id,name',
                'shift:id,name,start_time,end_time',
                'country:id,name',
                'state:id,name',
                'city:id,name',
                'user',
            ])
            ->filter($filters)
            ->latest();

        //        $generalSetting = DB::table('general_settings')->latest()->first();

        //        if (Auth::check() && $generalSetting?->staff_access === 'own') {
        //            $query->where('user_id', Auth::id());
        //        }

        return $query->paginate($perPage);
    }

    /**
     * Get a lightweight list of active employee options.
     *
     * @param  int|null  $warehouseId  Optional warehouse ID to filter employees (e.g. via user.warehouse_id when applicable).
     * @return Collection A collection of arrays containing 'value' (id) and 'label' (name).
     */
    public function getOptions(?int $warehouseId = null): Collection
    {
        $query = Employee::active()->select('id', 'name')->orderBy('name');

        if ($warehouseId !== null) {
            $query->whereHas('user', fn ($q) => $q->where('warehouse_id', $warehouseId));
        }

        return $query->get()
            ->map(fn (Employee $employee) => [
                'value' => $employee->id,
                'label' => $employee->name,
            ]);
    }

    /**
     * Create a newly registered employee and manage associated user account and files.
     * Extracts the nested 'user' array to generate system access and sync roles/permissions.
     *
     * @param  array<string, mixed>  $data
     */
    public function createEmployee(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
                $data['image'] = $path;
                $data['image_url'] = $this->uploadService->url($path);
            }

            if (! empty($data['user']) && is_array($data['user'])) {
                $userData = $data['user'];

                $user = User::query()->create([
                    'name' => $data['name'],
                    'email' => $data['email'],
                    'phone_number' => $data['phone_number'] ?? null,
                    'username' => $userData['username'],
                    'password' => Hash::make($userData['password']),
                    'image' => $data['image'] ?? null,
                    'image_url' => $data['image_url'] ?? null,
                    'is_active' => true,
                ]);

                if (! empty($userData['roles']) || ! empty($userData['permissions'])) {
                    $this->userRolePermissionService->assignRolesAndPermissions(
                        $user,
                        $userData['roles'] ?? [],
                        $userData['permissions'] ?? []
                    );
                }

                $data['user_id'] = $user->id;
            }

            unset($data['user']);

            return Employee::query()->create($data);
        });
    }

    /**
     * Update an existing employee, their associated files, and their linked User account.
     * Intelligently creates a user if added later, or updates the existing one.
     * Synchronizes Spatie roles and permissions seamlessly.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateEmployee(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($employee->image) {
                    $this->uploadService->delete($employee->image);
                }
                $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
                $data['image'] = $path;
                $data['image_url'] = $this->uploadService->url($path);
            }

            if (array_key_exists('user', $data) || $employee->user_id) {
                $userData = $data['user'];

                if (! $employee->user_id && ! empty($userData['password'])) {
                    $user = User::query()->create([
                        'name' => $data['name'] ?? $employee->name,
                        'email' => $data['email'] ?? $employee->email,
                        'phone_number' => $data['phone_number'] ?? $employee->phone_number,
                        'username' => $userData['username'] ?? null,
                        'password' => Hash::make($userData['password']),
                        'image' => $data['image'] ?? $employee->image,
                        'image_url' => $data['image_url'] ?? $employee->image_url,
                        'is_active' => true,
                    ]);

                    if (! empty($userData['roles']) || ! empty($userData['permissions'])) {
                        $this->userRolePermissionService->assignRolesAndPermissions(
                            $user,
                            $userData['roles'] ?? [],
                            $userData['permissions'] ?? []
                        );
                    }

                    $data['user_id'] = $user->id;
                } elseif ($employee->user_id) {
                    $user = User::query()->find($employee->user_id);
                    if ($user) {
                        $updatePayload = [
                            'name' => $data['name'] ?? $employee->name,
                            'email' => $data['email'] ?? $employee->email,
                            'phone_number' => $data['phone_number'] ?? $employee->phone_number,
                        ];

                        if (! empty($userData['username'])) {
                            $updatePayload['username'] = $userData['username'];
                        }

                        if (array_key_exists('image', $data)) {
                            $updatePayload['image'] = $data['image'];
                            $updatePayload['image_url'] = $data['image_url'] ?? null;
                        }

                        if (! empty($userData['password'])) {
                            $updatePayload['password'] = Hash::make($userData['password']);
                        }

                        $user->update($updatePayload);

                        if (isset($userData['roles']) || isset($userData['permissions'])) {
                            $this->userRolePermissionService->assignRolesAndPermissions(
                                $user,
                                $userData['roles'] ?? [],
                                $userData['permissions'] ?? []
                            );
                        }
                    }
                }
            }

            unset($data['user']);

            $employee->update($data);

            return $employee->fresh(['department', 'designation', 'company', 'user']);
        });
    }

    /**
     * Delete an employee, their files, and soft-delete their user account.
     */
    public function deleteEmployee(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            if ($employee->user_id) {
                $user = User::query()->find($employee->user_id);
                if ($user && $user->id > 2) {
                    $user->update(['is_active' => false]);
                }
            }

            if ($employee->image) {
                $this->uploadService->delete($employee->image);
            }

            $employee->delete();
        });
    }

    /**
     * Bulk delete multiple employees safely.
     *
     * @param  array<int>  $ids
     */
    public function bulkDeleteEmployees(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $employees = Employee::query()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($employees as $employee) {
                $this->deleteEmployee($employee);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Import multiple employees from an uploaded file.
     */
    public function importEmployees(UploadedFile $file): void
    {
        ExcelFacade::import(new EmployeesImport, $file);
    }

    /**
     * Download an employees CSV template.
     *
     * @throws RuntimeException
     */
    public function download(): string
    {
        $fileName = 'employees-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template employees not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing employee data.
     *
     * @param  array<int>  $ids
     * @param  array<string>  $columns
     * @param  array{start_date?: string, end_date?: string}  $filters
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'employees_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new EmployeesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}

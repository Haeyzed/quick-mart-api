<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\EmployeesExport;
use App\Imports\EmployeesImport;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
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
     * @param UploadService $uploadService Service responsible for handling file uploads.
     * @param UserService $userService Service responsible for creating and syncing User accounts.
     * @param EmployeeDocumentService $employeeDocumentService Service for creating employee documents.
     * @param EmployeeOnboardingService $employeeOnboardingService Service for starting onboarding automatically.
     */
    public function __construct(
        private readonly UploadService $uploadService,
        private readonly UserService $userService,
        private readonly EmployeeDocumentService $employeeDocumentService,
        private readonly EmployeeOnboardingService $employeeOnboardingService
    ) {}

    /**
     * Get paginated employees based on filters.
     *
     * @param array<string, mixed> $filters
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $generalSetting = DB::table('general_settings')->latest()->first();

        // if (Auth::check()
        //     && !Auth::user()->hasRole('Super Admin')
        //     && $generalSetting?->staff_access === 'own') {
        //     $filters['user_id'] = Auth::id();
        // }

        return Employee::query()
            ->with(['department:id,name',
                    'designation:id,name',
                    'shift',
                    'user.roles:id,name',
                    'user.permissions:id,name',
                    'profile',
                    'employmentType',
                    'warehouse:id,name',
                    'workLocation:id,name',
                    'salaryStructure',
                    'reportingManager',
                    'documents'
            ])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get Employee Options for Dropdowns
     *
     * @param int|null $warehouseId
     * @return Collection
     */
    public function getOptions(?int $warehouseId = null): Collection
    {
        return Employee::active()
            ->select('id', 'name')
            ->when($warehouseId, fn ($q) => $q->whereHas('user', fn ($userQuery) => $userQuery->where('warehouse_id', $warehouseId)))
            ->orderBy('name')
            ->get()
            ->map(fn (Employee $employee) => [
                'value' => $employee->id,
                'label' => $employee->name,
            ]);
    }

    /**
     * Create a newly registered employee and manage associated accounts, profiles, documents, and onboarding.
     *
     * @param array<string, mixed> $data
     * @return Employee
     */
    public function create(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            // 1. Extract Nested Payloads
            $userData = $data['user'] ?? [];
            $profileData = $data['profile'] ?? [];
            $documentsData = $data['documents'] ?? [];
            $onboardingTemplateId = $data['onboarding_checklist_template_id'] ?? null;

            unset($data['user'], $data['profile'], $data['documents'], $data['onboarding_checklist_template_id']);

            // 2. Handle Image Upload First
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
                $data['image_path'] = $path;
                $data['image_url'] = $this->uploadService->url($path);
                unset($data['image']);
            }

            // 3. Create System User Account via UserService
            if (!empty($userData) && !empty($userData['password'])) {
                $userPayload = [
                    'name' => $data['name'],
                    'email' => $data['email'] ?? null,
                    'phone' => $data['phone_number'] ?? null,
                    'username' => $userData['username'] ?? null,
                    'password' => $userData['password'],
                    'image_path' => $data['image_path'] ?? null,
                    'image_url' => $data['image_url'] ?? null,
                    'is_active' => true,
                    'roles' => $userData['roles'] ?? [],
                    'permissions' => $userData['permissions'] ?? [],
                ];

                $user = $this->userService->create($userPayload);
                $data['user_id'] = $user->id;
            }

            // 4. Create Root Employee Record
            $employee = Employee::query()->create($data);

            // 5. Create Extended Employee Profile
            if (!empty($profileData)) {
                $employee->profile()->create($profileData);
            }

            // 6. Register Initial Documents
            foreach ($documentsData as $docPayload) {
                $docPayload['employee_id'] = $employee->id;
                $this->employeeDocumentService->create($docPayload);
            }

            // 7. Start Automated Onboarding
            if ($onboardingTemplateId) {
                $this->employeeOnboardingService->startOnboarding($employee->id, (int) $onboardingTemplateId);
            }

            return $employee->fresh([
                'department', 'designation', 'shift', 'user.roles', 'user.permissions',
                'profile', 'documents', 'employmentType', 'warehouse', 'workLocation', 'salaryStructure', 'reportingManager'
            ]);
        });
    }

    /**
     * Update an existing employee, their associated user account, profile, and documents safely.
     *
     * @param Employee $employee
     * @param array<string, mixed> $data
     * @return Employee
     */
    public function update(Employee $employee, array $data): Employee
    {
        return DB::transaction(function () use ($employee, $data) {
            // 1. Extract Nested Payloads
            $userData = $data['user'] ?? null;
            $profileData = $data['profile'] ?? null;
            $documentsData = $data['documents'] ?? null;

            unset($data['user'], $data['profile'], $data['documents']);

            // 2. Handle Image Replacement
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($employee->image_path) {
                    $this->uploadService->delete($employee->image_path);
                }
                $path = $this->uploadService->upload($data['image'], self::IMAGE_PATH);
                $data['image_path'] = $path;
                $data['image_url'] = $this->uploadService->url($path);
                unset($data['image']);
            }

            // 3. Manage/Sync Associated User Account via UserService
            if ($userData !== null || $employee->user_id) {
                $userData = $userData ?? [];

                if (!$employee->user_id && !empty($userData['password'])) {
                    // Scenario A: User didn't exist before, but HR wants to create one now
                    $userPayload = [
                        'name' => $data['name'] ?? $employee->name,
                        'email' => $data['email'] ?? $employee->email,
                        'phone' => $data['phone_number'] ?? $employee->phone_number,
                        'username' => $userData['username'] ?? null,
                        'password' => $userData['password'],
                        'image_path' => $data['image_path'] ?? $employee->image_path,
                        'image_url' => $data['image_url'] ?? $employee->image_url,
                        'is_active' => true,
                        'roles' => $userData['roles'] ?? [],
                        'permissions' => $userData['permissions'] ?? [],
                    ];

                    $user = $this->userService->create($userPayload);
                    $data['user_id'] = $user->id;
                } elseif ($employee->user_id) {
                    // Scenario B: Employee already has an account, sync updates
                    $user = User::query()->find($employee->user_id);

                    if ($user) {
                        $updatePayload = [
                            'name' => $data['name'] ?? $user->name,
                            'email' => $data['email'] ?? $user->email,
                            'phone_number' => $data['phone_number'] ?? $user->phone_number,
                        ];

                        if (!empty($userData['username'])) {
                            $updatePayload['username'] = $userData['username'];
                        }

                        // Sync updated image paths down to the user object
                        if (array_key_exists('image_path', $data)) {
                            $updatePayload['image_path'] = $data['image_path'];
                            $updatePayload['image_url'] = $data['image_url'] ?? null;
                        }

                        if (!empty($userData['password'])) {
                            $updatePayload['password'] = $userData['password'];
                        }

                        if (isset($userData['roles'])) {
                            $updatePayload['roles'] = $userData['roles'];
                        }

                        if (isset($userData['permissions'])) {
                            $updatePayload['permissions'] = $userData['permissions'];
                        }

                        $this->userService->update($user, $updatePayload);
                    }
                }
            }

            // 4. Update Root Employee
            $employee->update($data);

            // 5. Update or Create Extended Profile
            if ($profileData !== null) {
                $employee->profile()->updateOrCreate(['employee_id' => $employee->id], $profileData);
            }

            // 6. Update or Create Documents
            if ($documentsData !== null) {
                foreach ($documentsData as $docPayload) {
                    if (!empty($docPayload['id'])) {
                        // Find and update existing document belonging to this employee
                        $existingDoc = $employee->documents()->find($docPayload['id']);

                        // Add the strict instanceof check to satisfy the IDE / Static Analyzer
                        if ($existingDoc instanceof EmployeeDocument) {
                            $this->employeeDocumentService->update($existingDoc, $docPayload);
                        }
                    } else {
                        // Create a brand-new document
                        $docPayload['employee_id'] = $employee->id;
                        $this->employeeDocumentService->create($docPayload);
                    }
                }
            }

            return $employee->fresh([
                'department', 'designation', 'shift', 'user.roles', 'user.permissions',
                'profile', 'documents', 'employmentType', 'warehouse', 'workLocation', 'salaryStructure', 'reportingManager'
            ]);
        });
    }

    /**
     * Delete an employee, their files, and soft-delete their user account.
     *
     * @param Employee $employee
     * @return void
     */
    public function delete(Employee $employee): void
    {
        DB::transaction(function () use ($employee) {
            // Soft delete user account via UserService, safeguarding Super Admins
            if ($employee->user_id) {
                $user = User::query()->find($employee->user_id);
                if ($user && !$user->hasRole('Super Admin')) {
                    $this->userService->delete($user);
                }
            }

            if ($employee->image_path) {
                $this->uploadService->delete($employee->image_path);
            }

            $employee->delete();
        });
    }

    /**
     * Bulk delete multiple employees safely.
     *
     * @param array<int> $ids
     * @return int
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $employees = Employee::query()->whereIn('id', $ids)->get();
            $count = 0;

            foreach ($employees as $employee) {
                $this->delete($employee);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update the active status for multiple employees.
     *
     * @param array<int> $ids
     * @param bool $isActive
     * @return int
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return DB::transaction(function () use ($ids, $isActive) {
            return Employee::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
        });
    }

    /**
     * Import multiple employees from an uploaded file.
     *
     * @param UploadedFile $file
     * @return void
     */
    public function import(UploadedFile $file): void
    {
        ExcelFacade::import(new EmployeesImport, $file);
    }

    /**
     * Download an employees CSV template.
     *
     * @return string
     * @throws RuntimeException
     */
    public function download(): string
    {
        $fileName = 'employees-sample.csv';
        $path = app_path(self::TEMPLATE_PATH . '/' . $fileName);

        if (!File::exists($path)) {
            throw new RuntimeException('Template employees not found.');
        }

        return $path;
    }

    /**
     * Generate an export file containing employee data.
     *
     * @param array<int> $ids
     * @param string $format
     * @param array<string> $columns
     * @param array{start_date?: string, end_date?: string} $filters
     * @return string
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'employees_' . now()->timestamp;
        $relativePath = 'exports/' . $fileName . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
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

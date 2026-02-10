<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Employee;
use App\Traits\CheckPermissionsTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Service for Sale Agent (Employee with is_sale_agent) CRUD.
 */
class SaleAgentService extends BaseService
{
    use CheckPermissionsTrait;

    private const DEFAULT_SALE_AGENT_IMAGES_PATH = 'images/sale_agent';

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    public function getSaleAgent(Employee $employee): Employee
    {
        $this->requirePermission('sale-agents');

        $this->ensureIsSaleAgent($employee);

        return $employee->fresh(['department', 'designation', 'shift', 'user']);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Employee>
     */
    public function getSaleAgents(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('sale-agents');

        return Employee::query()
            ->saleAgents()
            ->with(['department', 'designation', 'shift', 'user'])
            ->when(isset($filters['status']), fn ($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when(isset($filters['department_id']), fn ($q) => $q->where('department_id', $filters['department_id']))
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where(fn ($subQ) => $subQ
                    ->where('name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                    ->orWhere('staff_id', 'like', $term)
                );
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSaleAgent(array $data): Employee
    {
        $this->requirePermission('employees-create');

        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data = $this->handleImageUpload($data);
            }
            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_sale_agent'] = true;

            return Employee::create($data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSaleAgent(Employee $employee, array $data): Employee
    {
        $this->requirePermission('employees-update');

        $this->ensureIsSaleAgent($employee);

        return DB::transaction(function () use ($employee, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($employee->image) {
                    $this->uploadService->delete($employee->image);
                }
                $data = $this->handleImageUpload($data);
            }

            $data['is_sale_agent'] = true;
            $employee->update($data);

            return $employee->fresh(['department', 'designation', 'shift', 'user']);
        });
    }

    public function deleteSaleAgent(Employee $employee): void
    {
        $this->requirePermission('employees-delete');

        $this->ensureIsSaleAgent($employee);

        DB::transaction(function () use ($employee) {
            if ($employee->image) {
                $this->uploadService->delete($employee->image);
            }
            $employee->update(['is_active' => false]);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Employee>
     */
    public function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        $this->requirePermission('sale-agents');

        return Employee::query()
            ->saleAgents()
            ->where('is_active', true)
            ->with(['department', 'user'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkDeleteSaleAgents(array $ids): int
    {
        $this->requirePermission('employees-delete');

        $count = 0;
        $employees = Employee::whereIn('id', $ids)->saleAgents()->get();
        foreach ($employees as $employee) {
            $this->deleteSaleAgent($employee);
            $count++;
        }

        return $count;
    }

    private function ensureIsSaleAgent(Employee $employee): void
    {
        if (! $employee->is_sale_agent) {
            abort(404, 'Employee is not a sale agent.');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function handleImageUpload(array $data): array
    {
        $path = $this->uploadService->upload(
            $data['image'],
            config('storage.sale_agents.images', self::DEFAULT_SALE_AGENT_IMAGES_PATH)
        );

        $data['image'] = $path;

        return $data;
    }
}

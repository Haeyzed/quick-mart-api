<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\SaleAgentsExport;
use App\Imports\SaleAgentsImport;
use App\Models\Employee;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class SaleAgentService
 *
 * Handles business logic for Sale Agents (employees with is_sale_agent).
 */
class SaleAgentService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    private const DEFAULT_SALE_AGENT_IMAGES_PATH = 'images/sale_agent';

    public function __construct(
        private readonly UploadService $uploadService
    ) {
    }

    /**
     * Get paginated sale agents based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedSaleAgents(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Employee::query()
            ->saleAgents()
            ->with(['department', 'designation', 'shift', 'user'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of sale agent options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Employee::query()
            ->saleAgents()
            ->active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (Employee $e) => [
                'value' => $e->id,
                'label' => $e->name,
            ]);
    }

    /**
     * Get a single sale agent.
     */
    public function getSaleAgent(Employee $employee): Employee
    {
        $this->ensureIsSaleAgent($employee);
        return $employee->fresh(['department', 'designation', 'shift', 'user']);
    }

    private function ensureIsSaleAgent(Employee $employee): void
    {
        if (! $employee->is_sale_agent) {
            abort(404, 'Employee is not a sale agent.');
        }
    }

    /**
     * Create a new sale agent.
     *
     * @param array<string, mixed> $data
     */
    public function createSaleAgent(array $data): Employee
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data = $this->handleImageUpload($data);
            }
            $data['is_active'] = $data['is_active'] ?? true;
            $data['is_sale_agent'] = true;
            return Employee::query()->create($data);
        });
    }

    /**
     * @param array<string, mixed> $data
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

    /**
     * Update an existing sale agent.
     *
     * @param array<string, mixed> $data
     */
    public function updateSaleAgent(Employee $employee, array $data): Employee
    {
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

    /**
     * Delete (deactivate) a sale agent.
     */
    public function deleteSaleAgent(Employee $employee): void
    {
        $this->ensureIsSaleAgent($employee);
        DB::transaction(function () use ($employee) {
            if ($employee->image) {
                $this->uploadService->delete($employee->image);
            }
            $employee->update(['is_active' => false]);
        });
    }

    /**
     * Bulk delete (deactivate) sale agents.
     *
     * @param array<int> $ids
     * @return int Count of items processed.
     */
    public function bulkDeleteSaleAgents(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $employees = Employee::query()->whereIn('id', $ids)->saleAgents()->get();
            $count = 0;
            foreach ($employees as $employee) {
                $employee->update(['is_active' => false]);
                $count++;
            }
            return $count;
        });
    }

    /**
     * Bulk update status for sale agents.
     *
     * @param array<int> $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Employee::query()->whereIn('id', $ids)->saleAgents()->update(['is_active' => $isActive]);
    }

    /**
     * Get all active sale agents.
     *
     * @return EloquentCollection<int, Employee>
     */
    public function getAllActive(): EloquentCollection
    {
        return Employee::query()
            ->saleAgents()
            ->where('is_active', true)
            ->with(['department', 'user'])
            ->orderBy('name')
            ->get();
    }

    /**
     * Import sale agents from file.
     */
    public function importSaleAgents(UploadedFile $file): void
    {
        ExcelFacade::import(new SaleAgentsImport, $file);
    }

    /**
     * Download sale agents CSV template.
     */
    public function download(): string
    {
        $fileName = 'sale-agents-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);
        if (! File::exists($path)) {
            throw new RuntimeException('Sale agents import template not found.');
        }
        return $path;
    }

    /**
     * Generate sale agents export file.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'sale_agents_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;
        ExcelFacade::store(
            new SaleAgentsExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );
        return $relativePath;
    }
}

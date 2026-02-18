<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\IncomesExport;
use App\Imports\IncomesImport;
use App\Models\CashRegister;
use App\Models\Income;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class IncomeService
 *
 * Handles business logic for Incomes.
 */
class IncomeService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated incomes based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginatedIncomes(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = Income::query()
            ->with(['warehouse', 'incomeCategory', 'account', 'user'])
            ->filter($filters)
            ->latest('created_at');

        $user = Auth::user();
        if ($user && method_exists($user, 'isStaff') && $user->isStaff()) {
            $query->where('user_id', $user->id);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new income.
     *
     * @param array<string, mixed> $data
     */
    public function createIncome(array $data): Income
    {
        return DB::transaction(function () use ($data) {
            if (empty($data['reference_no'])) {
                $data['reference_no'] = 'ir-'.date('Ymd').'-'.date('His');
            }
            if (! isset($data['created_at'])) {
                $data['created_at'] = now();
            }
            if (! isset($data['user_id'])) {
                $data['user_id'] = Auth::id();
            }
            if (isset($data['user_id'], $data['warehouse_id'])) {
                $cr = CashRegister::query()
                    ->where('user_id', $data['user_id'])
                    ->where('warehouse_id', $data['warehouse_id'])
                    ->where('status', true)
                    ->first();
                if ($cr) {
                    $data['cash_register_id'] = $cr->id;
                }
            }
            return Income::query()->create($data);
        });
    }

    /**
     * Update an existing income.
     *
     * @param array<string, mixed> $data
     */
    public function updateIncome(Income $income, array $data): Income
    {
        return DB::transaction(function () use ($income, $data) {
            $income->update($data);
            return $income->fresh();
        });
    }

    /**
     * Delete an income.
     */
    public function deleteIncome(Income $income): void
    {
        DB::transaction(function () use ($income) {
            $income->delete();
        });
    }

    /**
     * Bulk delete incomes.
     *
     * @param array<int> $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteIncomes(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $incomes = Income::query()->whereIn('id', $ids)->get();
            $count = 0;
            foreach ($incomes as $income) {
                $income->delete();
                $count++;
            }
            return $count;
        });
    }

    /**
     * Import incomes from file.
     */
    public function importIncomes(UploadedFile $file): void
    {
        ExcelFacade::import(new IncomesImport, $file);
    }

    /**
     * Download incomes CSV template.
     */
    public function download(): string
    {
        $fileName = 'incomes-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);
        if (! File::exists($path)) {
            throw new RuntimeException('Incomes import template not found.');
        }
        return $path;
    }

    /**
     * Generate incomes export file.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     * @param array<string, mixed> $filters
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'incomes_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;
        ExcelFacade::store(
            new IncomesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );
        return $relativePath;
    }
}

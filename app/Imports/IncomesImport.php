<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Income;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class IncomesImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading, SkipsEmptyRows
{
    public function model(array $row): ?Income
    {
        $amount = isset($row['amount']) ? (float) $row['amount'] : 0;
        if ($amount <= 0) {
            return null;
        }
        $ref = trim((string) ($row['reference_no'] ?? ''));
        if ($ref === '') {
            $ref = 'ir-'.date('Ymd').'-'.date('His');
        }
        $userId = isset($row['user_id']) ? (int) $row['user_id'] : 1;
        $warehouseId = (int) ($row['warehouse_id'] ?? 1);
        return new Income([
            'reference_no' => $ref,
            'income_category_id' => (int) ($row['income_category_id'] ?? 1),
            'warehouse_id' => $warehouseId,
            'account_id' => isset($row['account_id']) ? (int) $row['account_id'] : null,
            'user_id' => $userId,
            'cash_register_id' => null,
            'amount' => $amount,
            'note' => $row['note'] ?? null,
            'created_at' => isset($row['created_at']) ? $row['created_at'] : now(),
        ]);
    }

    public function rules(): array
    {
        return [
            'income_category_id' => ['required', 'integer', 'exists:income_categories,id'],
            'warehouse_id' => ['required', 'integer', 'exists:warehouses,id'],
            'amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function batchSize(): int
    {
        return 500;
    }

    public function chunkSize(): int
    {
        return 500;
    }
}

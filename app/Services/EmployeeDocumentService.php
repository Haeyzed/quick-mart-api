<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmployeeDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeDocumentService
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeDocument::query()->with(['documentType', 'employee:id,name,employee_code'])->latest();
        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', (int) $filters['employee_id']);
        }
        if (! empty($filters['document_type_id'])) {
            $query->where('document_type_id', (int) $filters['document_type_id']);
        }
        if (isset($filters['expired']) && $filters['expired']) {
            $query->whereNotNull('expiry_date')->where('expiry_date', '<', now()->toDateString());
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): EmployeeDocument
    {
        return DB::transaction(fn () => EmployeeDocument::query()->create($data));
    }

    public function update(EmployeeDocument $document, array $data): EmployeeDocument
    {
        return DB::transaction(function () use ($document, $data) {
            $document->update($data);

            return $document->fresh(['documentType', 'employee']);
        });
    }

    public function delete(EmployeeDocument $document): void
    {
        DB::transaction(fn () => $document->delete());
    }
}

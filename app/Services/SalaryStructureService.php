<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SalaryStructure;
use App\Models\SalaryStructureItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class SalaryStructureService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = SalaryStructure::query()->withCount('structureItems')->latest();

        if (isset($filters['is_active'])) {
            $query->when(
                $filters['is_active'],
                fn ($q) => $q->active(),
                fn ($q) => $q->where('is_active', false)
            );
        }
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('name', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    public function getOptions(): Collection
    {
        return SalaryStructure::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (SalaryStructure $s) => [
                'value' => $s->id,
                'label' => $s->name,
            ]);
    }

    public function create(array $data): SalaryStructure
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'] ?? [];
            unset($data['items']);

            $structure = SalaryStructure::query()->create($data);

            foreach ($items as $item) {
                SalaryStructureItem::query()->create([
                    'salary_structure_id' => $structure->id,
                    'salary_component_id' => $item['salary_component_id'],
                    'amount' => $item['amount'] ?? 0,
                    'percentage' => $item['percentage'] ?? null,
                ]);
            }

            return $structure->fresh(['structureItems.salaryComponent']);
        });
    }

    public function update(SalaryStructure $salaryStructure, array $data): SalaryStructure
    {
        return DB::transaction(function () use ($salaryStructure, $data) {
            $items = $data['items'] ?? null;
            unset($data['items']);

            $salaryStructure->update($data);

            if (is_array($items)) {
                $salaryStructure->structureItems()->delete();
                foreach ($items as $item) {
                    SalaryStructureItem::query()->create([
                        'salary_structure_id' => $salaryStructure->id,
                        'salary_component_id' => $item['salary_component_id'],
                        'amount' => $item['amount'] ?? 0,
                        'percentage' => $item['percentage'] ?? null,
                    ]);
                }
            }

            return $salaryStructure->fresh(['structureItems.salaryComponent']);
        });
    }

    public function delete(SalaryStructure $salaryStructure): void
    {
        DB::transaction(fn () => $salaryStructure->delete());
    }

    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn () => SalaryStructure::query()->whereIn('id', $ids)->delete());
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SalaryComponent;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class SalaryComponentService
 *
 * Handles core business logic for Salary Components (earnings/deductions).
 */
class SalaryComponentService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<SalaryComponent>
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = SalaryComponent::query()->latest();

        if (isset($filters['is_active'])) {
            $query->when(
                $filters['is_active'],
                fn ($q) => $q->active(),
                fn ($q) => $q->where('is_active', false)
            );
        }
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('name', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, array{value: int, label: string, type?: string}>
     */
    public function getOptions(): Collection
    {
        return SalaryComponent::active()
            ->select('id', 'name', 'type')
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->map(fn (SalaryComponent $c) => [
                'value' => $c->id,
                'label' => $c->name,
                'type' => $c->type,
            ]);
    }

    public function create(array $data): SalaryComponent
    {
        return DB::transaction(fn () => SalaryComponent::query()->create($data));
    }

    public function update(SalaryComponent $salaryComponent, array $data): SalaryComponent
    {
        return DB::transaction(function () use ($salaryComponent, $data) {
            $salaryComponent->update($data);

            return $salaryComponent->fresh();
        });
    }

    public function delete(SalaryComponent $salaryComponent): void
    {
        DB::transaction(fn () => $salaryComponent->delete());
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn () => SalaryComponent::query()->whereIn('id', $ids)->delete());
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmploymentType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class EmploymentTypeService
 *
 * Handles all core business logic and database interactions for Employment Types.
 */
class EmploymentTypeService
{
    /**
     * Get paginated employment types based on filters.
     *
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<EmploymentType>
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = EmploymentType::query()->latest();

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

    /**
     * Get a lightweight list of active employment type options.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return EmploymentType::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (EmploymentType $type) => [
                'value' => $type->id,
                'label' => $type->name,
            ]);
    }

    /**
     * Create a newly registered employment type.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): EmploymentType
    {
        return DB::transaction(fn () => EmploymentType::query()->create($data));
    }

    /**
     * Update an existing employment type.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(EmploymentType $employmentType, array $data): EmploymentType
    {
        return DB::transaction(function () use ($employmentType, $data) {
            $employmentType->update($data);

            return $employmentType->fresh();
        });
    }

    /**
     * Delete an employment type.
     */
    public function delete(EmploymentType $employmentType): void
    {
        DB::transaction(fn () => $employmentType->delete());
    }

    /**
     * Bulk delete employment types.
     *
     * @param  array<int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn () => EmploymentType::query()->whereIn('id', $ids)->delete());
    }

    /**
     * Bulk update active status.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return EmploymentType::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }
}

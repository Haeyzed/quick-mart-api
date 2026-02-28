<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\WorkLocation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class WorkLocationService
 *
 * Handles all core business logic and database interactions for Work Locations.
 */
class WorkLocationService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<WorkLocation>
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = WorkLocation::query()->latest();

        if (isset($filters['is_active'])) {
            $query->when(
                $filters['is_active'],
                fn ($q) => $q->active(),
                fn ($q) => $q->where('is_active', false)
            );
        }
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('name', 'like', $term)->orWhere('code', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return WorkLocation::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (WorkLocation $loc) => [
                'value' => $loc->id,
                'label' => $loc->name,
            ]);
    }

    public function create(array $data): WorkLocation
    {
        return DB::transaction(fn () => WorkLocation::query()->create($data));
    }

    public function update(WorkLocation $workLocation, array $data): WorkLocation
    {
        return DB::transaction(function () use ($workLocation, $data) {
            $workLocation->update($data);

            return $workLocation->fresh();
        });
    }

    public function delete(WorkLocation $workLocation): void
    {
        DB::transaction(fn () => $workLocation->delete());
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn () => WorkLocation::query()->whereIn('id', $ids)->delete());
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return WorkLocation::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }
}

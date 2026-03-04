<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JobOpening;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class JobOpeningService
 *
 * Handles all core business logic and database interactions for Job Openings.
 * Acts as the intermediary between the controllers and the database layer.
 */
class JobOpeningService
{
    /**
     * Get paginated job openings based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return JobOpening::query()
            ->with(['department:id,name', 'designation:id,name'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get job opening options for dropdowns (open status only).
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return JobOpening::query()
            ->where('status', 'open')
            ->select('id', 'title')
            ->orderBy('title')
            ->get()
            ->map(fn(JobOpening $jobOpening) => [
                'value' => $jobOpening->id,
                'label' => $jobOpening->title,
            ]);
    }

    /**
     * Create a newly registered job opening.
     *
     * @param array<string, mixed> $data The validated request data.
     * @return JobOpening The newly created JobOpening model instance.
     */
    public function create(array $data): JobOpening
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            return JobOpening::query()->create($data);
        });
    }

    /**
     * Update an existing job opening.
     *
     * @param JobOpening $jobOpening The job opening model instance to update.
     * @param array<string, mixed> $data The validated update data.
     * @return JobOpening The freshly updated JobOpening model instance.
     */
    public function update(JobOpening $jobOpening, array $data): JobOpening
    {
        return DB::transaction(function () use ($jobOpening, $data) {
            $jobOpening->update($data);

            return $jobOpening->fresh();
        });
    }

    /**
     * Bulk delete multiple job openings.
     *
     * @param array<int> $ids Array of job opening IDs to be deleted.
     * @return int The total count of successfully deleted job openings.
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return JobOpening::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Delete a job opening.
     */
    public function delete(JobOpening $jobOpening): void
    {
        DB::transaction(fn() => $jobOpening->delete());
    }
}

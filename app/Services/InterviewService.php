<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Interview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class InterviewService
 *
 * Handles all core business logic and database interactions for Interviews.
 * Acts as the intermediary between the controllers and the database layer.
 */
class InterviewService
{
    /**
     * Get paginated interviews based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return Interview::query()
            ->with(['candidate:id,name,email,job_opening_id', 'interviewer:id,name'])
            ->filter($filters)
            ->latest('scheduled_at')
            ->paginate($perPage);
    }

    /**
     * Create a newly registered interview.
     *
     * @param array<string, mixed> $data The validated request data.
     * @return Interview The newly created Interview model instance.
     */
    public function create(array $data): Interview
    {
        return DB::transaction(fn() => Interview::query()->create($data));
    }

    /**
     * Update an existing interview.
     *
     * @param Interview $interview The interview model instance to update.
     * @param array<string, mixed> $data The validated update data.
     * @return Interview The freshly updated Interview model instance.
     */
    public function update(Interview $interview, array $data): Interview
    {
        return DB::transaction(function () use ($interview, $data) {
            $interview->update($data);

            return $interview->fresh(['candidate', 'interviewer']);
        });
    }

    /**
     * Bulk delete multiple interviews.
     *
     * @param array<int> $ids Array of interview IDs to be deleted.
     * @return int The total count of successfully deleted interviews.
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Interview::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Delete an interview.
     */
    public function delete(Interview $interview): void
    {
        DB::transaction(fn() => $interview->delete());
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PerformanceReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class PerformanceReviewService
 *
 * Handles all core business logic and database interactions for Performance Reviews.
 * Acts as the intermediary between the controllers and the database layer.
 */
class PerformanceReviewService
{
    /**
     * Get paginated performance reviews based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return PerformanceReview::query()
            ->with(['employee:id,name,employee_code', 'reviewer:id,name', 'newDesignation:id,name'])
            ->filter($filters)
            ->latest('review_period_end')
            ->paginate($perPage);
    }

    /**
     * Create a newly registered performance review.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return PerformanceReview The newly created PerformanceReview model instance.
     */
    public function create(array $data): PerformanceReview
    {
        return DB::transaction(fn () => PerformanceReview::query()->create($data));
    }

    /**
     * Update an existing performance review.
     *
     * @param  PerformanceReview  $performanceReview  The performance review model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return PerformanceReview The freshly updated PerformanceReview model instance.
     */
    public function update(PerformanceReview $performanceReview, array $data): PerformanceReview
    {
        return DB::transaction(function () use ($performanceReview, $data) {
            $performanceReview->update($data);

            return $performanceReview->fresh(['employee', 'reviewer', 'newDesignation']);
        });
    }

    /**
     * Delete a performance review.
     */
    public function delete(PerformanceReview $performanceReview): void
    {
        DB::transaction(fn () => $performanceReview->delete());
    }

    /**
     * Bulk delete multiple performance reviews.
     *
     * @param  array<int>  $ids  Array of performance review IDs to be deleted.
     * @return int The total count of successfully deleted performance reviews.
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return PerformanceReview::query()->whereIn('id', $ids)->delete();
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PerformanceReview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PerformanceReviewService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<PerformanceReview>
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = PerformanceReview::query()
            ->with(['employee:id,name,employee_code', 'reviewer:id,name', 'newDesignation:id,name'])
            ->latest('review_period_end');

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', (int) $filters['employee_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): PerformanceReview
    {
        return DB::transaction(fn () => PerformanceReview::query()->create($data));
    }

    public function update(PerformanceReview $review, array $data): PerformanceReview
    {
        return DB::transaction(function () use ($review, $data) {
            $review->update($data);

            return $review->fresh(['employee', 'reviewer', 'newDesignation']);
        });
    }

    public function delete(PerformanceReview $review): void
    {
        DB::transaction(fn () => $review->delete());
    }
}

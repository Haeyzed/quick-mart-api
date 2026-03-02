<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Candidate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Class CandidateService
 *
 * Handles all core business logic and database interactions for Candidates.
 * Acts as the intermediary between the controllers and the database layer.
 */
class CandidateService
{
    /**
     * Get paginated candidates based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Candidate::query()->with(['jobOpening:id,title,status'])->latest();
        if (! empty($filters['job_opening_id'])) {
            $query->where('job_opening_id', (int) $filters['job_opening_id']);
        }
        if (! empty($filters['stage'])) {
            $query->where('stage', $filters['stage']);
        }
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('name', 'like', $term)->orWhere('email', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a newly registered candidate.
     *
     * @param  array<string, mixed>  $data  The validated request data.
     * @return Candidate The newly created Candidate model instance.
     */
    public function create(array $data): Candidate
    {
        return DB::transaction(function () use ($data) {
            $data['stage_updated_at'] = now();

            return Candidate::query()->create($data);
        });
    }

    /**
     * Update an existing candidate.
     *
     * @param  Candidate  $candidate  The candidate model instance to update.
     * @param  array<string, mixed>  $data  The validated update data.
     * @return Candidate The freshly updated Candidate model instance.
     */
    public function update(Candidate $candidate, array $data): Candidate
    {
        return DB::transaction(function () use ($candidate, $data) {
            if (isset($data['stage']) && $data['stage'] !== $candidate->stage) {
                $data['stage_updated_at'] = now();
            }
            $candidate->update($data);

            return $candidate->fresh(['jobOpening']);
        });
    }

    /**
     * Delete a candidate.
     */
    public function delete(Candidate $candidate): void
    {
        DB::transaction(fn () => $candidate->delete());
    }
}

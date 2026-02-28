<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Interview;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class InterviewService
{
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Interview::query()->with(['candidate:id,name,email,job_opening_id', 'interviewer:id,name'])->latest('scheduled_at');
        if (! empty($filters['candidate_id'])) {
            $query->where('candidate_id', (int) $filters['candidate_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function create(array $data): Interview
    {
        return DB::transaction(fn () => Interview::query()->create($data));
    }

    public function update(Interview $interview, array $data): Interview
    {
        return DB::transaction(function () use ($interview, $data) {
            $interview->update($data);

            return $interview->fresh(['candidate', 'interviewer']);
        });
    }

    public function delete(Interview $interview): void
    {
        DB::transaction(fn () => $interview->delete());
    }
}

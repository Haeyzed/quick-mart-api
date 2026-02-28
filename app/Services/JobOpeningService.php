<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\JobOpening;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JobOpeningService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = JobOpening::query()->with(['department:id,name', 'designation:id,name'])->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('title', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    public function getOptions(): Collection
    {
        return JobOpening::query()
            ->where('status', 'open')
            ->select('id', 'title')
            ->orderBy('title')
            ->get()
            ->map(fn (JobOpening $job) => [
                'value' => $job->id,
                'label' => $job->title,
            ]);
    }

    public function create(array $data): JobOpening
    {
        return DB::transaction(function () use ($data) {
            $data['created_by'] = $data['created_by'] ?? auth()->id();

            return JobOpening::query()->create($data);
        });
    }

    public function update(JobOpening $jobOpening, array $data): JobOpening
    {
        return DB::transaction(function () use ($jobOpening, $data) {
            $jobOpening->update($data);

            return $jobOpening->fresh();
        });
    }

    public function delete(JobOpening $jobOpening): void
    {
        DB::transaction(fn () => $jobOpening->delete());
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OnboardingChecklistTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class OnboardingChecklistTemplateService
{
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = OnboardingChecklistTemplate::query()->withCount('items')->latest();
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('name', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    public function getOptions(): Collection
    {
        return OnboardingChecklistTemplate::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn (OnboardingChecklistTemplate $t) => [
                'value' => $t->id,
                'label' => $t->name,
            ]);
    }

    public function create(array $data): OnboardingChecklistTemplate
    {
        return DB::transaction(fn () => OnboardingChecklistTemplate::query()->create($data));
    }

    public function update(OnboardingChecklistTemplate $template, array $data): OnboardingChecklistTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            $template->update($data);

            return $template->fresh();
        });
    }

    public function delete(OnboardingChecklistTemplate $template): void
    {
        DB::transaction(fn () => $template->delete());
    }
}

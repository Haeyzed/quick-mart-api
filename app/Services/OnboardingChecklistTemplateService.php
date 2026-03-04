<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\OnboardingChecklistTemplate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Class OnboardingChecklistTemplateService
 *
 * Handles all core business logic and database interactions for Onboarding Checklist Templates.
 * Acts as the intermediary between the controllers and the database layer.
 */
class OnboardingChecklistTemplateService
{
    /**
     * Get paginated onboarding checklist templates based on filters.
     *
     * @param array<string, mixed> $filters
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return OnboardingChecklistTemplate::query()
            ->withCount('items')
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get template options for dropdowns.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return OnboardingChecklistTemplate::query()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn(OnboardingChecklistTemplate $template) => [
                'value' => $template->id,
                'label' => $template->name,
            ]);
    }

    /**
     * Create a newly registered onboarding checklist template.
     *
     * @param array<string, mixed> $data The validated request data.
     * @return OnboardingChecklistTemplate The newly created model instance.
     */
    public function create(array $data): OnboardingChecklistTemplate
    {
        return DB::transaction(fn() => OnboardingChecklistTemplate::query()->create($data));
    }

    /**
     * Update an existing onboarding checklist template.
     *
     * @param OnboardingChecklistTemplate $template The template model instance to update.
     * @param array<string, mixed> $data The validated update data.
     * @return OnboardingChecklistTemplate The freshly updated model instance.
     */
    public function update(OnboardingChecklistTemplate $template, array $data): OnboardingChecklistTemplate
    {
        return DB::transaction(function () use ($template, $data) {
            $template->update($data);

            return $template->fresh();
        });
    }

    /**
     * Bulk delete multiple onboarding checklist templates.
     *
     * @param array<int> $ids Array of template IDs to be deleted.
     * @return int The total count of successfully deleted templates.
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return OnboardingChecklistTemplate::query()->whereIn('id', $ids)->delete();
        });
    }

    /**
     * Delete an onboarding checklist template.
     */
    public function delete(OnboardingChecklistTemplate $template): void
    {
        DB::transaction(fn() => $template->delete());
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EmployeeOnboarding;
use App\Models\EmployeeOnboardingItem;
use App\Models\OnboardingChecklistItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class EmployeeOnboardingService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<EmployeeOnboarding>
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = EmployeeOnboarding::query()
            ->with(['employee:id,name,employee_code', 'template:id,name'])
            ->latest();

        if (! empty($filters['employee_id'])) {
            $query->where('employee_id', (int) $filters['employee_id']);
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    public function startOnboarding(int $employeeId, int $templateId): EmployeeOnboarding
    {
        return DB::transaction(function () use ($employeeId, $templateId) {
            $onboarding = EmployeeOnboarding::query()->create([
                'employee_id' => $employeeId,
                'onboarding_checklist_template_id' => $templateId,
                'status' => 'in_progress',
                'started_at' => now(),
            ]);

            $items = OnboardingChecklistItem::query()
                ->where('onboarding_checklist_template_id', $templateId)
                ->orderBy('sort_order')
                ->get();

            foreach ($items as $item) {
                EmployeeOnboardingItem::query()->create([
                    'employee_onboarding_id' => $onboarding->id,
                    'onboarding_checklist_item_id' => $item->id,
                ]);
            }

            return $onboarding->fresh(['employee', 'template', 'items.checklistItem']);
        });
    }

    public function update(EmployeeOnboarding $onboarding, array $data): EmployeeOnboarding
    {
        return DB::transaction(function () use ($onboarding, $data) {
            $onboarding->update($data);
            if (isset($data['status']) && $data['status'] === 'completed') {
                $onboarding->update(['completed_at' => now()]);
            }

            return $onboarding->fresh(['employee', 'template', 'items.checklistItem']);
        });
    }

    public function completeItem(EmployeeOnboardingItem $item, ?string $notes = null): EmployeeOnboardingItem
    {
        return DB::transaction(function () use ($item, $notes) {
            $item->update(['completed_at' => now(), 'notes' => $notes ?? $item->notes]);
            $onboarding = $item->employeeOnboarding;
            $allDone = $onboarding->items()->whereNull('completed_at')->doesntExist();
            if ($allDone) {
                $onboarding->update(['status' => 'completed', 'completed_at' => now()]);
            }

            return $item->fresh(['checklistItem']);
        });
    }

    public function delete(EmployeeOnboarding $onboarding): void
    {
        DB::transaction(fn () => $onboarding->delete());
    }
}

<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeOnboardingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'onboarding_checklist_template_id' => $this->onboarding_checklist_template_id,
            'status' => $this->status,
            'started_at' => $this->started_at?->toIso8601String(),
            'completed_at' => $this->completed_at?->toIso8601String(),
            'employee' => $this->whenLoaded('employee', fn () => ['id' => $this->employee->id, 'name' => $this->employee->name, 'employee_code' => $this->employee->employee_code]),
            'template' => $this->whenLoaded('template', fn () => ['id' => $this->template->id, 'name' => $this->template->name]),
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($i) => [
                'id' => $i->id,
                'onboarding_checklist_item_id' => $i->onboarding_checklist_item_id,
                'completed_at' => $i->completed_at?->toIso8601String(),
                'notes' => $i->notes,
                'checklist_item' => $i->relationLoaded('checklistItem') ? ['id' => $i->checklistItem->id, 'title' => $i->checklistItem->title] : null,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

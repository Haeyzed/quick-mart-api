<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmployeeOnboardingItem extends Model
{
    protected $fillable = [
        'employee_onboarding_id',
        'onboarding_checklist_item_id',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
        ];
    }

    public function employeeOnboarding(): BelongsTo
    {
        return $this->belongsTo(EmployeeOnboarding::class);
    }

    public function checklistItem(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklistItem::class, 'onboarding_checklist_item_id');
    }
}

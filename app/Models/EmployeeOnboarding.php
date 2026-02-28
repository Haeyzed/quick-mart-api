<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeOnboarding extends Model
{
    protected $table = 'employee_onboarding';

    protected $fillable = [
        'employee_id',
        'onboarding_checklist_template_id',
        'status',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklistTemplate::class, 'onboarding_checklist_template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingItem::class, 'employee_onboarding_id');
    }
}

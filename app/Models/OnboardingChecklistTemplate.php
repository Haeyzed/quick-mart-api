<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingChecklistTemplate extends Model
{
    protected $fillable = [
        'name',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OnboardingChecklistItem::class, 'onboarding_checklist_template_id')->orderBy('sort_order');
    }

    public function employeeOnboardings(): HasMany
    {
        return $this->hasMany(EmployeeOnboarding::class, 'onboarding_checklist_template_id');
    }
}

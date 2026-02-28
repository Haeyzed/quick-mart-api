<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OnboardingChecklistItem extends Model
{
    protected $fillable = [
        'onboarding_checklist_template_id',
        'title',
        'sort_order',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(OnboardingChecklistTemplate::class, 'onboarding_checklist_template_id');
    }

    public function employeeOnboardingItems(): HasMany
    {
        return $this->hasMany(EmployeeOnboardingItem::class, 'onboarding_checklist_item_id');
    }
}

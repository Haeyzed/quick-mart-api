<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceReview extends Model
{
    protected $fillable = [
        'employee_id',
        'review_period_start',
        'review_period_end',
        'reviewer_id',
        'overall_rating',
        'status',
        'notes',
        'promotion_effective_date',
        'new_designation_id',
        'submitted_at',
    ];

    protected function casts(): array
    {
        return [
            'review_period_start' => 'date',
            'review_period_end' => 'date',
            'promotion_effective_date' => 'date',
            'submitted_at' => 'datetime',
            'overall_rating' => 'decimal:2',
        ];
    }

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function newDesignation(): BelongsTo
    {
        return $this->belongsTo(Designation::class, 'new_designation_id');
    }
}

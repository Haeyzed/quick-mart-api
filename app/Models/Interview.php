<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Interview extends Model
{
    protected $fillable = [
        'candidate_id',
        'scheduled_at',
        'duration_minutes',
        'interviewer_id',
        'feedback',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
        ];
    }

    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }

    public function interviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'interviewer_id');
    }
}

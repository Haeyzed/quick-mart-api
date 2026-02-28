<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Candidate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'job_opening_id',
        'name',
        'email',
        'phone',
        'source',
        'stage',
        'stage_updated_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'stage_updated_at' => 'datetime',
        ];
    }

    public function jobOpening(): BelongsTo
    {
        return $this->belongsTo(JobOpening::class);
    }

    public function interviews(): HasMany
    {
        return $this->hasMany(Interview::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PayrollRun
 *
 * Represents a payroll run for a given month/year (batch of payroll entries).
 *
 * @property int $id
 * @property string $month
 * @property int $year
 * @property string $status
 * @property int|null $generated_by
 */
class PayrollRun extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'month',
        'year',
        'status',
        'generated_by',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public function generatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    public function entries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class, 'payroll_run_id');
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Attendance Model
 *
 * Represents an attendance record for an employee.
 *
 * @property int $id
 * @property Carbon $date
 * @property int $employee_id
 * @property int $user_id
 * @property string|null $checkin
 * @property string|null $checkout
 * @property string $status
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read User $user
 *
 * @method static Builder|Attendance present()
 * @method static Builder|Attendance absent()
 * @method static Builder|Attendance late()
 */
class Attendance extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'date',
        'employee_id',
        'user_id',
        'checkin',
        'checkout',
        'status',
        'note',
    ];

    /**
     * Get the employee for this attendance.
     *
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the user who recorded this attendance.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include present attendances.
     */
    public function scopePresent(Builder $query): Builder
    {
        return $query->where('status', 'present');
    }

    /**
     * Scope a query to only include absent attendances.
     */
    public function scopeAbsent(Builder $query): Builder
    {
        return $query->where('status', 'absent');
    }

    /**
     * Scope a query to only include late attendances.
     */
    public function scopeLate(Builder $query): Builder
    {
        return $query->where('status', 'late');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'employee_id' => 'integer',
            'user_id' => 'integer',
        ];
    }
}

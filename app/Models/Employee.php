<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Employee Model
 *
 * Represents an employee in the organization.
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property int $department_id
 * @property int $designation_id
 * @property int $shift_id
 * @property float $basic_salary
 * @property string|null $email
 * @property string|null $phone_number
 * @property int|null $user_id
 * @property string $staff_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $country
 * @property bool $is_active
 * @property bool $is_sale_agent
 * @property float|null $sale_commission_percent
 * @property array|null $sales_target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Department $department
 * @property-read Designation $designation
 * @property-read Shift $shift
 * @property-read User|null $user
 * @property-read Collection<int, Payroll> $payrolls
 * @property-read Collection<int, Attendance> $attendances
 * @property-read Collection<int, Leave> $leaves
 * @property-read Collection<int, Overtime> $overtimes
 * @property-read Collection<int, EmployeeTransaction> $transactions
 *
 * @method static Builder|Employee active()
 * @method static Builder|Employee saleAgents()
 * @method static Builder|Employee filter(array $filters)
 */
class Employee extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'image',
        'department_id',
        'designation_id',
        'shift_id',
        'basic_salary',
        'email',
        'phone_number',
        'user_id',
        'staff_id',
        'address',
        'city',
        'country',
        'is_active',
        'is_sale_agent',
        'sale_commission_percent',
        'sales_target',
    ];

    /**
     * Get the department for this employee.
     *
     * @return BelongsTo<Department, self>
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the designation for this employee.
     *
     * @return BelongsTo<Designation, self>
     */
    public function designation(): BelongsTo
    {
        return $this->belongsTo(Designation::class);
    }

    /**
     * Get the shift for this employee.
     *
     * @return BelongsTo<Shift, self>
     */
    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    /**
     * Get the user account for this employee.
     *
     * @return BelongsTo<User, self>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the payrolls for this employee.
     *
     * @return HasMany<Payroll>
     */
    public function payrolls(): HasMany
    {
        return $this->hasMany(Payroll::class);
    }

    /**
     * Get the attendances for this employee.
     *
     * @return HasMany<Attendance>
     */
    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    /**
     * Get the leaves for this employee.
     *
     * @return HasMany<Leave>
     */
    public function leaves(): HasMany
    {
        return $this->hasMany(Leave::class);
    }

    /**
     * Get the overtimes for this employee.
     *
     * @return HasMany<Overtime>
     */
    public function overtimes(): HasMany
    {
        return $this->hasMany(Overtime::class);
    }

    /**
     * Get the transactions for this employee.
     *
     * @return HasMany<EmployeeTransaction>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(EmployeeTransaction::class);
    }

    /**
     * Scope a query to only include active employees.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include sale agents.
     */
    public function scopeSaleAgents(Builder $query): Builder
    {
        return $query->where('is_sale_agent', true);
    }

    /**
     * Scope a query to apply filters (status, search, department_id).
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->where('is_active', $filters['status'] === 'active')
            )
            ->when(
                ! empty($filters['department_id'] ?? null),
                fn (Builder $q) => $q->where('department_id', (int) $filters['department_id'])
            )
            ->when(
                ! empty($filters['search'] ?? null),
                function (Builder $q) use ($filters) {
                    $term = '%'.$filters['search'].'%';
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_number', 'like', $term)
                        ->orWhere('staff_id', 'like', $term)
                    );
                }
            );
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'department_id' => 'integer',
            'designation_id' => 'integer',
            'shift_id' => 'integer',
            'basic_salary' => 'float',
            'user_id' => 'integer',
            'is_active' => 'boolean',
            'is_sale_agent' => 'boolean',
            'sale_commission_percent' => 'float',
            'sales_target' => 'array',
        ];
    }
}

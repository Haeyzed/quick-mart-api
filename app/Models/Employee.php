<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Employee
 *
 * Represents an employee entity within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for employee entities.
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property string|null $image_url
 * @property int $department_id
 * @property int $designation_id
 * @property int $shift_id
 * @property float $basic_salary
 * @property string|null $email
 * @property string|null $phone_number
 * @property int|null $user_id
 * @property string $staff_id
 * @property string|null $address
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property bool $is_active
 * @property bool $is_sale_agent
 * @property float|null $sale_commission_percent
 * @property array|null $sales_target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static Builder|Employee query()
 * @method static Builder|Employee active()
 * @method static Builder|Employee saleAgents()
 * @method static Builder|Employee filter(array $filters)
 */
class Employee extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'image',
        'image_url',
        'department_id',
        'designation_id',
        'shift_id',
        'basic_salary',
        'user_id',
        'staff_id',
        'country_id',
        'state_id',
        'city_id',
        'address',
        'is_active',
        'is_sale_agent',
        'sale_commission_percent',
        'sales_target',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'department_id' => 'integer',
        'designation_id' => 'integer',
        'shift_id' => 'integer',
        'basic_salary' => 'float',
        'user_id' => 'integer',
        'country_id' => 'integer',
        'state_id' => 'integer',
        'city_id' => 'integer',
        'is_active' => 'boolean',
        'is_sale_agent' => 'boolean',
        'sale_commission_percent' => 'float',
        'sales_target' => 'array',
    ];

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query
     * @param  array<string, mixed>  $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                isset($filters['is_sale_agent']),
                fn (Builder $q) => $q->saleAgent()
            )
            ->when(
                ! empty($filters['department_id']),
                fn (Builder $q) => $q->where('department_id', (int) $filters['department_id'])
            )
            ->when(
                ! empty($filters['designation_id']),
                fn (Builder $q) => $q->where('designation_id', (int) $filters['designation_id'])
            )
            ->when(
                ! empty($filters['country_id']),
                fn (Builder $q) => $q->where('country_id', (int) $filters['country_id'])
            )
            ->when(
                ! empty($filters['state_id']),
                fn (Builder $q) => $q->where('state_id', (int) $filters['state_id'])
            )
            ->when(
                ! empty($filters['city_id']),
                fn (Builder $q) => $q->where('city_id', (int) $filters['city_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_number', 'like', $term)
                        ->orWhere('staff_id', 'like', $term)
                        ->orWhereHas('user', function ($q) use ($term) {
                            $q->where('name', 'like', $term)
                            ->orWhere('email', 'like', $term)
                            ->orWhere('phone_number', 'like', $term);
                        })
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
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
    public function scopeSaleAgent(Builder $query): Builder
    {
        return $query->where('is_sale_agent', true);
    }

    /**
     * Get the country associated with this biller.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state associated with this biller.
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the city associated with this biller.
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

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
}

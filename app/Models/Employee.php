<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use OwenIt\Auditing\Models\Audit;

/**
 * Class Employee
 *
 * Represents an employee entity within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for employee entities.
 *
 * @property int $id
 * @property string $name
 * @property string|null $image_path
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
 * @method static Builder|Employee newModelQuery()
 * @method static Builder|Employee newQuery()
 * @method static Builder|Employee query()
 * @method static Builder|Employee active()
 * @method static Builder|Employee saleAgents()
 * @method static Builder|Employee filter(array $filters)
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read City|null $city
 * @property-read Department $department
 * @property-read Designation $designation
 * @property-read Shift $shift
 * @property-read User|null $user
 * @property-read Collection<int, Payroll> $payrolls
 * @property-read int|null $payrolls_count
 * @property-read Collection<int, Attendance> $attendances
 * @property-read int|null $attendances_count
 * @property-read Collection<int, Leave> $leaves
 * @property-read int|null $leaves_count
 * @property-read Collection<int, Overtime> $overtimes
 * @property-read int|null $overtimes_count
 * @property-read Collection<int, EmployeeTransaction> $transactions
 * @property-read int|null $transactions_count
 * @property-read EmploymentType|null $employmentType
 * @property-read Employee|null $reportingManager
 * @property-read Collection<int, Employee> $subordinates
 * @property-read int|null $subordinates_count
 * @property-read Warehouse|null $warehouse
 * @property-read WorkLocation|null $workLocation
 * @property-read SalaryStructure|null $salaryStructure
 * @property-read EmployeeProfile|null $profile
 * @property-read Collection<int, EmployeeShiftAssignment> $shiftAssignments
 * @property-read int|null $shift_assignments_count
 * @property-read Collection<int, EmployeeDocument> $documents
 * @property-read int|null $documents_count
 * @property-read Collection<int, PerformanceReview> $performanceReviews
 * @property-read int|null $performance_reviews_count
 * @property-read Collection<int, EmployeeOnboarding> $employeeOnboardings
 * @property-read int|null $employee_onboardings_count
 * @property-read Collection<int, Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Employee customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Employee last30Days(string $column = 'created_at')
 * @method static Builder<static>|Employee last7Days(string $column = 'created_at')
 * @method static Builder<static>|Employee lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Employee lastYear(string $column = 'created_at')
 * @method static Builder<static>|Employee monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Employee onlyTrashed()
 * @method static Builder<static>|Employee quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Employee saleAgent()
 * @method static Builder<static>|Employee today(string $column = 'created_at')
 * @method static Builder<static>|Employee whereAddress($value)
 * @method static Builder<static>|Employee whereBasicSalary($value)
 * @method static Builder<static>|Employee whereCityId($value)
 * @method static Builder<static>|Employee whereConfirmationDate($value)
 * @method static Builder<static>|Employee whereCountryId($value)
 * @method static Builder<static>|Employee whereCreatedAt($value)
 * @method static Builder<static>|Employee whereDeletedAt($value)
 * @method static Builder<static>|Employee whereDepartmentId($value)
 * @method static Builder<static>|Employee whereDesignationId($value)
 * @method static Builder<static>|Employee whereEmail($value)
 * @method static Builder<static>|Employee whereEmployeeCode($value)
 * @method static Builder<static>|Employee whereEmploymentStatus($value)
 * @method static Builder<static>|Employee whereEmploymentTypeId($value)
 * @method static Builder<static>|Employee whereId($value)
 * @method static Builder<static>|Employee whereImage($value)
 * @method static Builder<static>|Employee whereImageUrl($value)
 * @method static Builder<static>|Employee whereIsActive($value)
 * @method static Builder<static>|Employee whereIsSaleAgent($value)
 * @method static Builder<static>|Employee whereJoiningDate($value)
 * @method static Builder<static>|Employee whereName($value)
 * @method static Builder<static>|Employee wherePhoneNumber($value)
 * @method static Builder<static>|Employee whereProbationEndDate($value)
 * @method static Builder<static>|Employee whereReportingManagerId($value)
 * @method static Builder<static>|Employee whereSalaryStructureId($value)
 * @method static Builder<static>|Employee whereSaleCommissionPercent($value)
 * @method static Builder<static>|Employee whereSalesTarget($value)
 * @method static Builder<static>|Employee whereShiftId($value)
 * @method static Builder<static>|Employee whereStaffId($value)
 * @method static Builder<static>|Employee whereStateId($value)
 * @method static Builder<static>|Employee whereUpdatedAt($value)
 * @method static Builder<static>|Employee whereUserId($value)
 * @method static Builder<static>|Employee whereWarehouseId($value)
 * @method static Builder<static>|Employee whereWorkLocationId($value)
 * @method static Builder<static>|Employee withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Employee withoutTrashed()
 * @method static Builder<static>|Employee yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Employee yesterday(string $column = 'current_at')
 * @property string|null $employee_code
 * @property int|null $employment_type_id
 * @property Carbon|null $joining_date
 * @property Carbon|null $confirmation_date
 * @property Carbon|null $probation_end_date
 * @property int|null $reporting_manager_id
 * @property int|null $warehouse_id
 * @property int|null $work_location_id
 * @property int|null $salary_structure_id
 * @property string $employment_status
 * @method static Builder<static>|Employee whereImagePath($value)
 * @mixin Eloquent
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
        'employee_code',
        'name',
        'email',
        'phone_number',
        'image_path',
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
        'employment_type_id',
        'joining_date',
        'confirmation_date',
        'probation_end_date',
        'reporting_manager_id',
        'warehouse_id',
        'work_location_id',
        'salary_structure_id',
        'employment_status',
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
        'employment_type_id' => 'integer',
        'joining_date' => 'date',
        'confirmation_date' => 'date',
        'probation_end_date' => 'date',
        'reporting_manager_id' => 'integer',
        'warehouse_id' => 'integer',
        'work_location_id' => 'integer',
        'salary_structure_id' => 'integer',
        'employment_status' => 'string',
    ];

    /**
     * Boot the model and register creating listener for employee_code.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (Employee $employee): void {
            if (empty($employee->employee_code)) {
                $prefix = 'EMP';
                $last = static::withTrashed()->orderByDesc('id')->value('id') ?? 0;
                $employee->employee_code = $prefix . str_pad((string)($last + 1), 5, '0', STR_PAD_LEFT);
            }
        });
    }

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param Builder $query The Eloquent query builder instance.
     * @param array<string, mixed> $filters An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                isset($filters['is_sale_agent']),
                fn(Builder $q) => $q->saleAgent()
            )
            ->when(
                !empty($filters['user_id']),
                fn(Builder $q) => $q->where('user_id', (int)$filters['user_id'])
            )
            ->when(
                !empty($filters['department_id']),
                fn(Builder $q) => $q->where('department_id', (int)$filters['department_id'])
            )
            ->when(
                !empty($filters['designation_id']),
                fn(Builder $q) => $q->where('designation_id', (int)$filters['designation_id'])
            )
            ->when(
                !empty($filters['country_id']),
                fn(Builder $q) => $q->where('country_id', (int)$filters['country_id'])
            )
            ->when(
                !empty($filters['state_id']),
                fn(Builder $q) => $q->where('state_id', (int)$filters['state_id'])
            )
            ->when(
                !empty($filters['city_id']),
                fn(Builder $q) => $q->where('city_id', (int)$filters['city_id'])
            )
            ->when(
                !empty($filters['warehouse_id']),
                fn(Builder $q) => $q->where('warehouse_id', (int)$filters['warehouse_id'])
            )
            ->when(
                !empty($filters['employment_status']),
                fn(Builder $q) => $q->where('employment_status', $filters['employment_status'])
            )
            ->when(
                !empty($filters['employee_code']),
                fn(Builder $q) => $q->where('employee_code', 'like', '%' . $filters['employee_code'] . '%')
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn(Builder $subQ) => $subQ
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
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
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

    /**
     * Get the employment type for this employee.
     *
     * @return BelongsTo<EmploymentType, self>
     */
    public function employmentType(): BelongsTo
    {
        return $this->belongsTo(EmploymentType::class);
    }

    /**
     * Get the reporting manager (employee) for this employee.
     *
     * @return BelongsTo<Employee, self>
     */
    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the subordinates (employees reporting to this employee).
     *
     * @return HasMany<Employee, self>
     */
    public function subordinates(): HasMany
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    /**
     * Get the warehouse assigned to this employee.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the work location for this employee.
     *
     * @return BelongsTo<WorkLocation, self>
     */
    public function workLocation(): BelongsTo
    {
        return $this->belongsTo(WorkLocation::class);
    }

    /**
     * Get the salary structure for this employee.
     *
     * @return BelongsTo<SalaryStructure, self>
     */
    public function salaryStructure(): BelongsTo
    {
        return $this->belongsTo(SalaryStructure::class);
    }

    /**
     * Get the extended profile for this employee.
     *
     * @return HasOne<EmployeeProfile, self>
     */
    public function profile(): HasOne
    {
        return $this->hasOne(EmployeeProfile::class);
    }

    /**
     * Get the shift assignments (history) for this employee.
     *
     * @return HasMany<EmployeeShiftAssignment, self>
     */
    public function shiftAssignments(): HasMany
    {
        return $this->hasMany(EmployeeShiftAssignment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(EmployeeDocument::class);
    }

    public function performanceReviews(): HasMany
    {
        return $this->hasMany(PerformanceReview::class);
    }

    public function employeeOnboardings(): HasMany
    {
        return $this->hasMany(EmployeeOnboarding::class);
    }
}

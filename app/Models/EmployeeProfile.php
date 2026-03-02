<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class EmployeeProfile
 *
 * Extended profile data for an employee (PII, bank, tax, emergency contact).
 * Handles the underlying data structure, relationships, and specific query scopes for employee profile entities.
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string|null $marital_status
 * @property string|null $address
 * @property array|null $emergency_contact
 * @property string|null $national_id
 * @property string|null $tax_number
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|EmployeeProfile newModelQuery()
 * @method static Builder|EmployeeProfile newQuery()
 * @method static Builder|EmployeeProfile query()
 * @method static Builder|EmployeeProfile filter(array $filters)
 *
 * @property-read Employee $employee
 *
 * @method static Builder<static>|EmployeeProfile customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile last30Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile last7Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile lastYear(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile monthToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile today(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile whereAccountNumber($value)
 * @method static Builder<static>|EmployeeProfile whereAddress($value)
 * @method static Builder<static>|EmployeeProfile whereBankName($value)
 * @method static Builder<static>|EmployeeProfile whereCreatedAt($value)
 * @method static Builder<static>|EmployeeProfile whereDateOfBirth($value)
 * @method static Builder<static>|EmployeeProfile whereEmergencyContact($value)
 * @method static Builder<static>|EmployeeProfile whereEmployeeId($value)
 * @method static Builder<static>|EmployeeProfile whereGender($value)
 * @method static Builder<static>|EmployeeProfile whereId($value)
 * @method static Builder<static>|EmployeeProfile whereMaritalStatus($value)
 * @method static Builder<static>|EmployeeProfile whereNationalId($value)
 * @method static Builder<static>|EmployeeProfile whereTaxNumber($value)
 * @method static Builder<static>|EmployeeProfile whereUpdatedAt($value)
 * @method static Builder<static>|EmployeeProfile yearToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeProfile yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class EmployeeProfile extends Model
{
    use FilterableByDates;

    protected $table = 'employee_profiles';

    protected $fillable = [
        'employee_id',
        'date_of_birth',
        'gender',
        'marital_status',
        'address',
        'emergency_contact',
        'national_id',
        'tax_number',
        'bank_name',
        'account_number',
    ];

    protected function casts(): array
    {
        return [
            'date_of_birth' => 'date',
            'emergency_contact' => 'array',
        ];
    }

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                ! empty($filters['employee_id']),
                fn (Builder $q) => $q->where('employee_id', (int) $filters['employee_id'])
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('national_id', 'like', $term)
                        ->orWhere('tax_number', 'like', $term)
                        ->orWhere('account_number', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

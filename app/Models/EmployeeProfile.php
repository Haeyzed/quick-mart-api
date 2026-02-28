<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class EmployeeProfile
 *
 * Extended profile data for an employee (PII, bank, tax, emergency contact).
 *
 * @property int $id
 * @property int $employee_id
 * @property \Illuminate\Support\Carbon|null $date_of_birth
 * @property string|null $gender
 * @property string|null $marital_status
 * @property string|null $address
 * @property array|null $emergency_contact
 * @property string|null $national_id
 * @property string|null $tax_number
 * @property string|null $bank_name
 * @property string|null $account_number
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class EmployeeProfile extends Model
{
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
     * @return BelongsTo<Employee, self>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }
}

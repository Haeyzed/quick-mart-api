<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * Class EmployeeDocument
 * 
 * Represents a single document associated with an employee, such as ID cards,
 * certifications, or contracts. Handles storage metadata, expiry checks, and
 * filtering by employee, document type, and expiry status.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $document_type_id
 * @property string|null $name
 * @property string|null $file_path
 * @property string|null $file_url
 * @property Carbon|null $issue_date
 * @property Carbon|null $expiry_date
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read DocumentType $documentType
 * @method static Builder|EmployeeDocument newModelQuery()
 * @method static Builder|EmployeeDocument newQuery()
 * @method static Builder|EmployeeDocument query()
 * @method static Builder|EmployeeDocument filter(array $filters)
 * @method static Builder<static>|EmployeeDocument customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument last30Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument last7Days(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument lastYear(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument monthToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument today(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument yearToDate(string $column = 'created_at')
 * @method static Builder<static>|EmployeeDocument yesterday(string $column = 'current_at')
 * @method static Builder<static>|EmployeeDocument whereCreatedAt($value)
 * @method static Builder<static>|EmployeeDocument whereDocumentTypeId($value)
 * @method static Builder<static>|EmployeeDocument whereEmployeeId($value)
 * @method static Builder<static>|EmployeeDocument whereExpiryDate($value)
 * @method static Builder<static>|EmployeeDocument whereFilePath($value)
 * @method static Builder<static>|EmployeeDocument whereFileUrl($value)
 * @method static Builder<static>|EmployeeDocument whereId($value)
 * @method static Builder<static>|EmployeeDocument whereIssueDate($value)
 * @method static Builder<static>|EmployeeDocument whereName($value)
 * @method static Builder<static>|EmployeeDocument whereNotes($value)
 * @method static Builder<static>|EmployeeDocument whereUpdatedAt($value)
 * @mixin Eloquent
 */
class EmployeeDocument extends Model
{
    use FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'employee_id',
        'document_type_id',
        'name',
        'file_path',
        'file_url',
        'issue_date',
        'expiry_date',
        'notes',
    ];

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
                !empty($filters['employee_id']),
                fn(Builder $q) => $q->where('employee_id', (int)$filters['employee_id'])
            )
            ->when(
                !empty($filters['document_type_id']),
                fn(Builder $q) => $q->where('document_type_id', (int)$filters['document_type_id'])
            )
            ->when(
                isset($filters['expired']) && $filters['expired'],
                fn(Builder $q) => $q->whereNotNull('expiry_date')->where('expiry_date', '<', now()->toDateString())
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Get the employee that owns this document.
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Get the document type for this employee document.
     */
    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    /**
     * Determine if the document is expired based on its expiry date.
     */
    public function isExpired(): bool
    {
        return $this->expiry_date !== null && $this->expiry_date->isPast();
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
        ];
    }
}

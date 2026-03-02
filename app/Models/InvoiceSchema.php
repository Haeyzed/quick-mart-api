<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class InvoiceSchema
 *
 * Represents invoice numbering schema configuration. Handles the underlying data
 * structure, relationships, and specific query scopes for invoice schema entities.
 *
 * @property int $id
 * @property string $prefix
 * @property int $number_of_digit
 * @property int $start_number
 * @property int|null $last_invoice_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static Builder|InvoiceSchema newModelQuery()
 * @method static Builder|InvoiceSchema newQuery()
 * @method static Builder|InvoiceSchema query()
 * @method static Builder|InvoiceSchema filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|InvoiceSchema customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema last30Days(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema last7Days(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema lastYear(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema monthToDate(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema today(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema whereCreatedAt($value)
 * @method static Builder<static>|InvoiceSchema whereId($value)
 * @method static Builder<static>|InvoiceSchema whereLastInvoiceNumber($value)
 * @method static Builder<static>|InvoiceSchema whereNumberOfDigit($value)
 * @method static Builder<static>|InvoiceSchema wherePrefix($value)
 * @method static Builder<static>|InvoiceSchema whereStartNumber($value)
 * @method static Builder<static>|InvoiceSchema whereUpdatedAt($value)
 * @method static Builder<static>|InvoiceSchema yearToDate(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSchema yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class InvoiceSchema extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_schemas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'prefix',
        'number_of_digit',
        'start_number',
        'last_invoice_number',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'number_of_digit' => 'integer',
        'start_number' => 'integer',
        'last_invoice_number' => 'integer',
    ];

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
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('prefix', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }
}

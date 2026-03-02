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
 * Class Barcode
 *
 * Represents a barcode format configuration. Handles the underlying data
 * structure, relationships, and specific query scopes for barcode entities.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float|null $width
 * @property float|null $height
 * @property float|null $paper_width
 * @property float|null $paper_height
 * @property float|null $top_margin
 * @property float|null $left_margin
 * @property float|null $row_distance
 * @property float|null $col_distance
 * @property int|null $stickers_in_one_row
 * @property int $is_continuous
 * @property int|null $stickers_in_one_sheet
 * @property int|null $is_custom
 *
 * @method static Builder|Barcode newModelQuery()
 * @method static Builder|Barcode newQuery()
 * @method static Builder|Barcode query()
 * @method static Builder|Barcode default()
 * @method static Builder|Barcode filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|Barcode customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Barcode last30Days(string $column = 'created_at')
 * @method static Builder<static>|Barcode last7Days(string $column = 'created_at')
 * @method static Builder<static>|Barcode lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Barcode lastYear(string $column = 'created_at')
 * @method static Builder<static>|Barcode monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Barcode quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Barcode today(string $column = 'created_at')
 * @method static Builder<static>|Barcode whereColDistance($value)
 * @method static Builder<static>|Barcode whereCreatedAt($value)
 * @method static Builder<static>|Barcode whereDescription($value)
 * @method static Builder<static>|Barcode whereHeight($value)
 * @method static Builder<static>|Barcode whereId($value)
 * @method static Builder<static>|Barcode whereIsContinuous($value)
 * @method static Builder<static>|Barcode whereIsCustom($value)
 * @method static Builder<static>|Barcode whereIsDefault($value)
 * @method static Builder<static>|Barcode whereLeftMargin($value)
 * @method static Builder<static>|Barcode whereName($value)
 * @method static Builder<static>|Barcode wherePaperHeight($value)
 * @method static Builder<static>|Barcode wherePaperWidth($value)
 * @method static Builder<static>|Barcode whereRowDistance($value)
 * @method static Builder<static>|Barcode whereStickersInOneRow($value)
 * @method static Builder<static>|Barcode whereStickersInOneSheet($value)
 * @method static Builder<static>|Barcode whereTopMargin($value)
 * @method static Builder<static>|Barcode whereUpdatedAt($value)
 * @method static Builder<static>|Barcode whereWidth($value)
 * @method static Builder<static>|Barcode yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Barcode yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class Barcode extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

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
                isset($filters['is_default']),
                fn (Builder $q) => $q->default()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('description', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include default barcode.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }
}

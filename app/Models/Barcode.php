<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Barcode Model
 * 
 * Represents a barcode format configuration.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Barcode default()
 * @property numeric|null $width
 * @property numeric|null $height
 * @property numeric|null $paper_width
 * @property numeric|null $paper_height
 * @property numeric|null $top_margin
 * @property numeric|null $left_margin
 * @property numeric|null $row_distance
 * @property numeric|null $col_distance
 * @property int|null $stickers_in_one_row
 * @property int $is_continuous
 * @property int|null $stickers_in_one_sheet
 * @property int|null $is_custom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static Builder<static>|Barcode newModelQuery()
 * @method static Builder<static>|Barcode newQuery()
 * @method static Builder<static>|Barcode query()
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
 * @mixin \Eloquent
 */
class Barcode extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

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

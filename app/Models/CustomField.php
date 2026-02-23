<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * CustomField Model
 * 
 * Represents a custom field that can be added to various entities.
 *
 * @property int $id
 * @property string $belongs_to
 * @property string $name
 * @property string $type
 * @property string|null $default_value
 * @property string|null $option_value
 * @property string|null $grid_value
 * @property bool $is_table
 * @property bool $is_invoice
 * @property bool $is_required
 * @property bool $is_admin
 * @property bool $is_disable
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereBelongsTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereGridValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsDisable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereOptionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CustomField extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'belongs_to',
        'name',
        'type',
        'default_value',
        'option_value',
        'grid_value',
        'is_table',
        'is_invoice',
        'is_required',
        'is_admin',
        'is_disable',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_table' => 'boolean',
            'is_invoice' => 'boolean',
            'is_required' => 'boolean',
            'is_admin' => 'boolean',
            'is_disable' => 'boolean',
        ];
    }
}

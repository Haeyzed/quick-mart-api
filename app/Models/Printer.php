<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Printer Model
 * 
 * Represents a printer configuration for a warehouse.
 *
 * @property int $id
 * @property string $name
 * @property int $warehouse_id
 * @property string $connection_type
 * @property string $capability_profile
 * @property string|null $char_per_line
 * @property string|null $ip_address
 * @property string|null $port
 * @property string|null $path
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Warehouse $warehouse
 * @method static Builder|Printer active()
 * @property int $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read string $capability_profile_str
 * @property-read string $connection_type_str
 * @method static Builder<static>|Printer newModelQuery()
 * @method static Builder<static>|Printer newQuery()
 * @method static Builder<static>|Printer query()
 * @method static Builder<static>|Printer whereCapabilityProfile($value)
 * @method static Builder<static>|Printer whereCharPerLine($value)
 * @method static Builder<static>|Printer whereConnectionType($value)
 * @method static Builder<static>|Printer whereCreatedAt($value)
 * @method static Builder<static>|Printer whereCreatedBy($value)
 * @method static Builder<static>|Printer whereId($value)
 * @method static Builder<static>|Printer whereIpAddress($value)
 * @method static Builder<static>|Printer whereName($value)
 * @method static Builder<static>|Printer wherePath($value)
 * @method static Builder<static>|Printer wherePort($value)
 * @method static Builder<static>|Printer whereUpdatedAt($value)
 * @method static Builder<static>|Printer whereWarehouseId($value)
 * @mixin \Eloquent
 */
class Printer extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = ['id'];

    /**
     * Get the warehouse for this printer.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the capability profile string representation.
     */
    public function getCapabilityProfileStrAttribute(): string
    {
        $profiles = self::capabilityProfiles();

        return $profiles[$this->capability_profile] ?? $this->capability_profile;
    }

    /**
     * Get available capability profiles.
     *
     * @return array<string, string>
     */
    public static function capabilityProfiles(): array
    {
        return [
            'default' => 'Default',
            'simple' => 'Simple',
            'SP2000' => 'Star SP2000 Series',
            'TEP-200M' => 'EPOS TEP200M Series',
            'TM-U220' => 'Epson TM-U220',
            'RP326' => 'Rongta RP326',
            'P822D' => 'PBM P822D',
        ];
    }

    /**
     * Get the connection type string representation.
     */
    public function getConnectionTypeStrAttribute(): string
    {
        $types = self::connectionTypes();

        return $types[$this->connection_type] ?? $this->connection_type;
    }

    /**
     * Get available connection types.
     *
     * @return array<string, string>
     */
    public static function connectionTypes(): array
    {
        return [
            'network' => 'Network',
            'windows' => 'Windows',
            'linux' => 'Linux',
        ];
    }

    /**
     * Scope a query to only include active printers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'warehouse_id' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}

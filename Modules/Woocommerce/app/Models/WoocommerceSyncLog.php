<?php

declare(strict_types=1);

namespace Modules\Woocommerce\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * WoocommerceSyncLog Model
 *
 * Represents a synchronization log entry for WooCommerce operations.
 *
 * @property int $id
 * @property string $sync_type
 * @property string $operation
 * @property int $records
 * @property int|null $synced_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read User|null $syncer
 */
class WoocommerceSyncLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sync_type',
        'operation',
        'records',
        'synced_by',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'records' => 'integer',
            'synced_by' => 'integer',
        ];
    }

    /**
     * Get the user who performed this sync.
     *
     * @return BelongsTo<User, self>
     */
    public function syncer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'synced_by');
    }
}

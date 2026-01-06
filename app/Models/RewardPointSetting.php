<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * RewardPointSetting Model
 *
 * Represents reward points system configuration settings.
 *
 * @property int $id
 * @property float $per_point_amount
 * @property float $minimum_amount
 * @property int $duration
 * @property string $type
 * @property bool $is_active
 * @property float $redeem_amount_per_unit_rp
 * @property float $min_order_total_for_redeem
 * @property int $min_redeem_point
 * @property int $max_redeem_point
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class RewardPointSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'per_point_amount',
        'minimum_amount',
        'duration',
        'type',
        'is_active',
        'redeem_amount_per_unit_rp',
        'min_order_total_for_redeem',
        'min_redeem_point',
        'max_redeem_point',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'per_point_amount' => 'float',
            'minimum_amount' => 'float',
            'duration' => 'integer',
            'is_active' => 'boolean',
            'redeem_amount_per_unit_rp' => 'float',
            'min_order_total_for_redeem' => 'float',
            'min_redeem_point' => 'integer',
            'max_redeem_point' => 'integer',
        ];
    }
}


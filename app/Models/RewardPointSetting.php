<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

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
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMaxRedeemPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMinOrderTotalForRedeem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMinRedeemPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMinimumAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting wherePerPointAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereRedeemAmountPerUnitRp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class RewardPointSetting extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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

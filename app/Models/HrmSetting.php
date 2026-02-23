<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * HrmSetting Model
 * 
 * Represents HRM (Human Resource Management) system settings.
 *
 * @property int $id
 * @property string $checkin
 * @property string $checkout
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereCheckin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereCheckout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class HrmSetting extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'checkin',
        'checkout',
    ];
}

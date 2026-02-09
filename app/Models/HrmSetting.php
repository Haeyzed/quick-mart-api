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

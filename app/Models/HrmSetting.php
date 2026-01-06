<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

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
class HrmSetting extends Model
{
    use HasFactory;

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


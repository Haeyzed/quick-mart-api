<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * MailSetting Model
 *
 * Represents email/mail server configuration settings.
 * Supports multiple mail configurations with one marked as default.
 *
 * @property int $id
 * @property string $driver
 * @property string $host
 * @property int $port
 * @property string $from_address
 * @property string $from_name
 * @property string $username
 * @property string $password
 * @property string|null $encryption
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class MailSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'driver',
        'host',
        'port',
        'from_address',
        'from_name',
        'username',
        'password',
        'encryption',
        'is_default',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Scope to get the default mail setting.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        static::saving(function (MailSetting $model) {
            if ($model->is_default) {
                $query = static::query()->where('is_default', true);
                if ($model->exists) {
                    $query->whereKeyNot($model->getKey());
                }
                $query->update(['is_default' => false]);
            }
        });
    }
}


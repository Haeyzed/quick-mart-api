<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class MailSetting
 *
 * Represents email/mail server configuration settings. Handles the underlying data
 * structure, relationships, and specific query scopes for mail setting entities.
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
 *
 * @method static Builder|MailSetting newModelQuery()
 * @method static Builder|MailSetting newQuery()
 * @method static Builder|MailSetting query()
 * @method static Builder|MailSetting default()
 * @method static Builder|MailSetting filter(array $filters)
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|MailSetting customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|MailSetting last30Days(string $column = 'created_at')
 * @method static Builder<static>|MailSetting last7Days(string $column = 'created_at')
 * @method static Builder<static>|MailSetting lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|MailSetting lastYear(string $column = 'created_at')
 * @method static Builder<static>|MailSetting monthToDate(string $column = 'created_at')
 * @method static Builder<static>|MailSetting quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|MailSetting today(string $column = 'created_at')
 * @method static Builder<static>|MailSetting whereCreatedAt($value)
 * @method static Builder<static>|MailSetting whereDriver($value)
 * @method static Builder<static>|MailSetting whereEncryption($value)
 * @method static Builder<static>|MailSetting whereFromAddress($value)
 * @method static Builder<static>|MailSetting whereFromName($value)
 * @method static Builder<static>|MailSetting whereHost($value)
 * @method static Builder<static>|MailSetting whereId($value)
 * @method static Builder<static>|MailSetting whereIsDefault($value)
 * @method static Builder<static>|MailSetting wherePassword($value)
 * @method static Builder<static>|MailSetting wherePort($value)
 * @method static Builder<static>|MailSetting whereUpdatedAt($value)
 * @method static Builder<static>|MailSetting whereUsername($value)
 * @method static Builder<static>|MailSetting yearToDate(string $column = 'created_at')
 * @method static Builder<static>|MailSetting yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class MailSetting extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'port' => 'integer',
        'is_default' => 'boolean',
    ];

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

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_default']),
                fn (Builder $q) => $q->default()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('from_name', 'like', $term)
                        ->orWhere('from_address', 'like', $term)
                        ->orWhere('host', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope to get the default mail setting.
     */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}

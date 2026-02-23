<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Biller
 * 
 * Represents a biller entity.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone_number
 * @property string|null $company_name
 * @property string|null $vat_number
 * @property string|null $address
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property string|null $postal_code
 * @property string|null $image
 * @property string|null $image_url
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Biller newModelQuery()
 * @method static Builder|Biller newQuery()
 * @method static Builder|Biller query()
 * @method static Builder|Biller active()
 * @method static Builder|Biller filter(array $filters)
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read City|null $city
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static Builder<static>|Biller customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Biller last30Days(string $column = 'created_at')
 * @method static Builder<static>|Biller last7Days(string $column = 'created_at')
 * @method static Builder<static>|Biller lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Biller lastYear(string $column = 'created_at')
 * @method static Builder<static>|Biller monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Biller onlyTrashed()
 * @method static Builder<static>|Biller quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Biller today(string $column = 'created_at')
 * @method static Builder<static>|Biller whereAddress($value)
 * @method static Builder<static>|Biller whereCityId($value)
 * @method static Builder<static>|Biller whereCompanyName($value)
 * @method static Builder<static>|Biller whereCountryId($value)
 * @method static Builder<static>|Biller whereCreatedAt($value)
 * @method static Builder<static>|Biller whereDeletedAt($value)
 * @method static Builder<static>|Biller whereEmail($value)
 * @method static Builder<static>|Biller whereId($value)
 * @method static Builder<static>|Biller whereImage($value)
 * @method static Builder<static>|Biller whereImageUrl($value)
 * @method static Builder<static>|Biller whereIsActive($value)
 * @method static Builder<static>|Biller whereName($value)
 * @method static Builder<static>|Biller wherePhoneNumber($value)
 * @method static Builder<static>|Biller wherePostalCode($value)
 * @method static Builder<static>|Biller whereStateId($value)
 * @method static Builder<static>|Biller whereUpdatedAt($value)
 * @method static Builder<static>|Biller whereVatNumber($value)
 * @method static Builder<static>|Biller withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Biller withoutTrashed()
 * @method static Builder<static>|Biller yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Biller yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Biller extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'company_name',
        'vat_number',
        'address',
        'country_id',
        'state_id',
        'city_id',
        'postal_code',
        'image',
        'image_url',
        'is_active',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Scope a query to apply filters.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->active()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn (Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_number', 'like', $term)
                        ->orWhere('company_name', 'like', $term)
                        ->orWhere('vat_number', 'like', $term)
                    );
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active billers.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the country.
     *
     * @return BelongsTo<Country, $this>
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the state.
     *
     * @return BelongsTo<State, $this>
     */
    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }

    /**
     * Get the city.
     *
     * @return BelongsTo<City, $this>
     */
    public function city(): BelongsTo
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Get the users associated with this biller.
     *
     * @return HasMany<User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the sales associated with this biller.
     */
    public function sales(): HasMany
    {
        return $this->hasMany(Sale::class);
    }
}

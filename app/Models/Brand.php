<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Brand
 * 
 * Represents a product brand within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for brand entities.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $page_title
 * @property string|null $image
 * @property string|null $image_url
 * @property bool $is_active
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Brand newModelQuery()
 * @method static Builder|Brand newQuery()
 * @method static Builder|Brand query()
 * @method static Builder|Brand active()
 * @method static Builder|Brand filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static Builder<static>|Brand customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|Brand last30Days(string $column = 'created_at')
 * @method static Builder<static>|Brand last7Days(string $column = 'created_at')
 * @method static Builder<static>|Brand lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|Brand lastYear(string $column = 'created_at')
 * @method static Builder<static>|Brand monthToDate(string $column = 'created_at')
 * @method static Builder<static>|Brand onlyTrashed()
 * @method static Builder<static>|Brand quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|Brand today(string $column = 'created_at')
 * @method static Builder<static>|Brand whereCreatedAt($value)
 * @method static Builder<static>|Brand whereDeletedAt($value)
 * @method static Builder<static>|Brand whereId($value)
 * @method static Builder<static>|Brand whereImage($value)
 * @method static Builder<static>|Brand whereImageUrl($value)
 * @method static Builder<static>|Brand whereIsActive($value)
 * @method static Builder<static>|Brand whereName($value)
 * @method static Builder<static>|Brand wherePageTitle($value)
 * @method static Builder<static>|Brand whereShortDescription($value)
 * @method static Builder<static>|Brand whereSlug($value)
 * @method static Builder<static>|Brand whereUpdatedAt($value)
 * @method static Builder<static>|Brand withTrashed(bool $withTrashed = true)
 * @method static Builder<static>|Brand withoutTrashed()
 * @method static Builder<static>|Brand yearToDate(string $column = 'created_at')
 * @method static Builder<static>|Brand yesterday(string $column = 'current_at')
 * @mixin \Eloquent
 */
class Brand extends Model implements AuditableContract
{
    use HasFactory, Auditable, SoftDeletes, FilterableByDates;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'page_title',
        'image',
        'image_url',
        'is_active',
        'start_date',
        'end_date',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Bootstrap the model and its traits.
     * * Registers model events. Hooked into the 'saving' event to automatically
     * generate a unique slug if one is not provided before the model is saved to the database.
     * * @return void
     */
    protected static function booted(): void
    {
        static::saving(static function (Brand $brand): void {
            if (empty($brand->slug)) {
                $brand->slug = $brand->generateUniqueSlug($brand->name);
            }
        });
    }

    /**
     * Generate a unique slug for the brand based on its name.
     * * Converts the name to a URL-friendly slug. If the slug already exists in
     * the database, it appends a numeric counter (e.g., brand-name-1, brand-name-2)
     * until it finds a unique value.
     *
     * @param string $name The original brand name to convert.
     * @param string|null $existingSlug An optional manually provided slug to check.
     * @return string A guaranteed unique slug string.
     */
    public function generateUniqueSlug(string $name, ?string $existingSlug = null): string
    {
        $slug = $existingSlug ?: Str::slug($name);

        if (!$this->slugExists($slug)) {
            return $slug;
        }

        $originalSlug = $slug;
        $count = 1;

        while ($this->slugExists($slug)) {
            $slug = "{$originalSlug}-{$count}";
            $count++;
        }

        return $slug;
    }

    /**
     * Check if the given slug already exists in the database.
     * * Ensures that when updating an existing model, its own current slug
     * doesn't trigger a false positive for duplication.
     *
     * @param string $slug The slug to check for uniqueness.
     * @return bool True if the slug exists, false if it is available.
     */
    protected function slugExists(string $slug): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn(Builder $query) => $query->whereKeyNot($this->getKey()))
            ->exists();
    }

    /**
     * Scope a query to apply dynamic filters.
     * * Applies filters for active status, search terms (checking name and slug),
     * and date ranges via the FilterableByDates trait.
     *
     * @param Builder $query The Eloquent query builder instance.
     * @param array<string, mixed> $filters An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['is_active']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                !empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where(fn(Builder $subQ) => $subQ
                        ->where('name', 'like', $term)
                        ->orWhere('slug', 'like', $term)
                    );
                }
            )
            ->customRange(
                !empty($filters['start_date']) ? $filters['start_date'] : null,
                !empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

    /**
     * Scope a query to only include active brands.
     *
     * @param Builder $query The Eloquent query builder instance.
     * @return Builder The modified query builder instance.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the products associated with this brand.
     * * Defines a one-to-many relationship linking this brand to its respective products.
     *
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

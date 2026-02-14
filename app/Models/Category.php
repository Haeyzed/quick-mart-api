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
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Category
 *
 * Represents a product category.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $page_title
 * @property string|null $image
 * @property string|null $image_url
 * @property string|null $icon
 * @property string|null $icon_url
 * @property int|null $parent_id
 * @property bool $is_active
 * @property bool $featured
 * @property bool $is_sync_disable
 * @property int|null $woocommerce_category_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 *
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category query()
 * @method static Builder|Category active()
 * @method static Builder|Category featured()
 * @method static Builder|Category syncDisabled()
 * @method static Builder|Category filter(array $filters)
 */

class Category extends Model implements AuditableContract
{
    use Auditable, HasFactory, SoftDeletes, FilterableByDates;

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
        'icon',
        'icon_url',
        'parent_id',
        'is_active',
        'featured',
        'is_sync_disable',
        'woocommerce_category_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'is_sync_disable' => 'boolean',
        'parent_id' => 'integer',
        'woocommerce_category_id' => 'integer',
    ];

    /**
     * Bootstrap the model and its traits.
     */
    protected static function booted(): void
    {
        static::saving(static function (Category $category): void {
            if (empty($category->slug)) {
                $category->slug = $category->generateUniqueSlug($category->name);
            }
        });
    }

    /**
     * Generate a unique slug for the category.
     */
    public function generateUniqueSlug(string $name, ?string $existingSlug = null): string
    {
        $slug = $existingSlug ?: Str::slug($name);

        if (! $this->slugExists($slug)) {
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
     * Check if the slug exists, excluding the current ID.
     */
    protected function slugExists(string $slug): bool
    {
        return static::query()
            ->where('slug', $slug)
            ->when($this->exists, fn(Builder $query) => $query->whereKeyNot($this->getKey()))
            ->exists();
    }

    /**
     * Scope a query to apply filters.
     *
     * @param Builder $query
     * @param array<string, mixed> $filters
     * @return Builder
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn(Builder $q) => $q->active()
            )
            ->when(
                isset($filters['featured']),
                fn(Builder $q) => $q->featured()
            )
            ->when(
                isset($filters['is_sync_disable']),
                fn(Builder $q) => $q->syncDisabled()
            )
            ->when(
                isset($filters['parent_id']),
                fn(Builder $q) => $q->where('parent_id', $filters['parent_id'])
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
     * Scope a query to only include active categories.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include featured categories.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('featured', true);
    }

    /**
     * Scope a query to only include categories with sync disabled.
     */
    public function scopeSyncDisabled(Builder $query): Builder
    {
        return $query->where('is_sync_disable', true);
    }

    /**
     * Get the parent category.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    /**
     * Get the child categories.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Get the products associated with this category.
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}

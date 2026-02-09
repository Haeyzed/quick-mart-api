<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class Category
 *
 * Represents a product category with hierarchical structure support.
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
 * @property-read string $status
 * @property-read string $featured_status
 * @property-read string $sync_status
 * @property-read Category|null $parent
 * @property-read Collection<int, Category> $children
 * @property-read Collection<int, Product> $products
 *
 * @method static Builder|Category active()
 * @method static Builder|Category featured()
 * @method static Builder|Category root()
 */
class Category extends Model implements AuditableContract
{
    use Auditable, HasFactory;

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
        'parent_id' => 'integer',
        'is_active' => 'boolean',
        'featured' => 'boolean',
        'is_sync_disable' => 'boolean',
        'woocommerce_category_id' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['status'];

    /**
     * Boot the model.
     */
    protected static function booted(): void
    {
        // Automatically generate slug on saving if not present
        static::saving(function (Category $category) {
            $category->slug = $category->generateUniqueSlug(
                $category->name,
                $category->slug
            );
        });
    }

    /**
     * Generate a unique slug for the category.
     * Uses iterative loop to avoid recursion stack overflow.
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
            $slug = "{$originalSlug}-".$count++;
        }

        return $slug;
    }

    /**
     * Check if the slug exists for another category, excluding the current model when persisted.
     *
     * Handles both new (unsaved) and existing records. For new records,
     * where('id', '!=', null) would produce invalid SQL semantics, so we only
     * exclude the current model when it has been persisted.
     *
     * @param  string  $slug  The slug to check for uniqueness.
     * @return bool True if another category already uses this slug.
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }

    /* -----------------------------------------------------------------
     |  Relationships
     | -----------------------------------------------------------------
     */

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /* -----------------------------------------------------------------
     |  Scopes & Helpers
     | -----------------------------------------------------------------
     */

    /**
     * Check if this is a root category.
     */
    public function isRoot(): bool
    {
        return $this->parent_id === null;
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
     * Scope a query to only include root categories.
     */
    public function scopeRoot(Builder $query): Builder
    {
        return $query->whereNull('parent_id');
    }

    /* -----------------------------------------------------------------
     |  Accessors
     | -----------------------------------------------------------------
     */

    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    public function getFeaturedStatusAttribute(): string
    {
        return $this->featured ? 'featured' : 'not featured';
    }

    public function getSyncStatusAttribute(): string
    {
        return $this->is_sync_disable ? 'disabled' : 'enabled';
    }
}

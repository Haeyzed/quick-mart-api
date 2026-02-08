<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class Brand
 *
 * Represents a product brand in the catalog.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $page_title
 * @property string|null $image
 * @property string|null $image_url
 * @property bool $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read string $status
 */
class Brand extends Model
{
    use HasFactory, SoftDeletes;

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
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = ['status'];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted(): void
    {
        // Automatically generate slug on saving if not present
        static::saving(function (Brand $brand) {
            $brand->slug = $brand->generateUniqueSlug($brand->name, $brand->slug);
        });
    }

    /**
     * Generate a unique slug for the brand.
     * Replaces recursion with a performant loop.
     *
     * @param string $name
     * @param string|null $existingSlug
     * @return string
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
            $slug = "{$originalSlug}-" . $count++;
        }

        return $slug;
    }

    /**
     * Check if the slug exists for another brand, excluding the current model when persisted.
     *
     * Handles both new (unsaved) and existing records. For new records,
     * where('id', '!=', null) would produce invalid SQL semantics, so we only
     * exclude the current model when it has been persisted.
     *
     * @param string $slug The slug to check for uniqueness.
     * @return bool True if another brand already uses this slug.
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        if ($this->exists) {
            $query->whereKeyNot($this->getKey());
        }

        return $query->exists();
    }

    /**
     * Get the human-readable status.
     *
     * @return string
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * Get the products associated with this brand.
     *
     * @return HasMany<Product>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
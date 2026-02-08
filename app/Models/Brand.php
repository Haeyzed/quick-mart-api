<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * Class Brand
 * * @property int $id
 * @property string $name
 * @property string|null $slug
 * @property string|null $image
 * @property string|null $image_url
 * @property bool $is_active
 * @property-read string $status Virtual attribute
 */
class Brand extends Model
{
    use SoftDeletes;

    /** @var array */
    protected $fillable = [
        'name', 'image', 'image_url', 'page_title', 'short_description', 'slug', 'is_active'
    ];

    /** @var array */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** @var array */
    protected $appends = ['status'];

    /**
     * Automate logic during model lifecycle.
     */
    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($brand) => $brand->ensureSlugExists());
    }

    /**
     * Ensure a unique slug is generated if none provided.
     */
    public function ensureSlugExists(): void
    {
        if (empty($this->slug)) {
            $this->slug = static::generateUniqueSlug($this->name);
        }
    }

    /**
     * Recursive unique slug generator.
     */
    public static function generateUniqueSlug(string $name): string
    {
        $slug = Str::slug($name);
        $original = $slug;
        $count = 1;

        while (static::where('slug', $slug)->exists()) {
            $slug = "{$original}-" . $count++;
        }

        return $slug;
    }

    /**
     * Accessor for human-readable status.
     */
    public function getStatusAttribute(): string
    {
        return $this->is_active ? 'active' : 'inactive';
    }

    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
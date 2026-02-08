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
 * @property bool $is_active
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
        // Ensure slug is generated for both creates and updates if missing
        static::saving(fn($brand) => $brand->ensureSlugExists());
    }

    /**
     * Ensure a unique slug is generated.
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

        while (static::where('slug', $slug)->where('id', '!=', request()->route('brand')?->id)->exists()) {
            $slug = "{$original}-" . $count++;
        }

        return $slug;
    }

    /**
     * @return HasMany
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
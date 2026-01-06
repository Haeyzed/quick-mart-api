<?php

declare(strict_types=1);

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * Page Model
 *
 * Represents a custom page in the ecommerce system.
 *
 * @property int $id
 * @property string $page_name
 * @property string|null $description
 * @property string $slug
 * @property string|null $template
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $og_title
 * @property string|null $og_image
 * @property string|null $og_descripton
 * @property bool $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, PageWidget> $pageWidgets
 *
 * @method static Builder|Page active()
 */
class Page extends Model
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'page_name',
        'description',
        'slug',
        'template',
        'meta_title',
        'meta_description',
        'og_title',
        'og_image',
        'og_descripton',
        'status',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    /**
     * Get the page widgets for this page.
     *
     * @return HasMany<PageWidget>
     */
    public function pageWidgets(): HasMany
    {
        return $this->hasMany(PageWidget::class);
    }

    /**
     * Scope a query to only include active pages.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', true);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * MenuItem Model
 *
 * Represents a menu item in a navigation menu.
 *
 * @property int $id
 * @property string $title
 * @property string $name
 * @property string $slug
 * @property string $type
 * @property string|null $target
 * @property int $menu_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Menu $menu
 */
class MenuItem extends Model
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'menu_items';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'name',
        'slug',
        'type',
        'target',
        'menu_id',
        'created_at',
        'updated_at',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'menu_id' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Get the menu that owns this menu item.
     *
     * @return BelongsTo<Menu, self>
     */
    public function menu(): BelongsTo
    {
        return $this->belongsTo(Menu::class);
    }
}

<?php

declare(strict_types=1);

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Slider Model
 *
 * Represents a slider/banner for the ecommerce frontend.
 *
 * @property int $id
 * @property string|null $title
 * @property string|null $link
 * @property string|null $image1
 * @property string|null $image2
 * @property string|null $image3
 * @property int $order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Slider extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sliders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'link',
        'image1',
        'image2',
        'image3',
        'order',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'order' => 'integer',
        ];
    }
}

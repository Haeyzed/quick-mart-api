<?php

declare(strict_types=1);

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * Widget Model
 *
 * Represents a widget configuration for the ecommerce frontend.
 *
 * @property int $id
 * @property string $name
 * @property string $location
 * @property int $order
 * @property string|null $feature_title
 * @property string|null $feature_secondary_title
 * @property string|null $feature_icon
 * @property string|null $site_info_name
 * @property string|null $site_info_description
 * @property string|null $site_info_address
 * @property string|null $site_info_phone
 * @property string|null $site_info_email
 * @property string|null $site_info_hours
 * @property string|null $newsletter_title
 * @property string|null $newsletter_text
 * @property string|null $quick_links_title
 * @property string|null $quick_links_menu
 * @property string|null $text_title
 * @property string|null $text_content
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Widget extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'widgets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'location',
        'order',
        'feature_title',
        'feature_secondary_title',
        'feature_icon',
        'site_info_name',
        'site_info_description',
        'site_info_address',
        'site_info_phone',
        'site_info_email',
        'site_info_hours',
        'newsletter_title',
        'newsletter_text',
        'quick_links_title',
        'quick_links_menu',
        'text_title',
        'text_content',
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

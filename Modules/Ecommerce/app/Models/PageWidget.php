<?php

declare(strict_types=1);

namespace Modules\Ecommerce\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;

/**
 * PageWidget Model
 *
 * Represents a widget configuration for a specific page.
 *
 * @property int $id
 * @property string $name
 * @property int $page_id
 * @property int $order
 * @property string|null $product_category_title
 * @property int|null $product_category_id
 * @property string|null $product_category_type
 * @property bool|null $product_category_slider_loop
 * @property bool|null $product_category_slider_autoplay
 * @property int|null $product_category_limit
 * @property int|null $tab_product_collection_id
 * @property string|null $tab_product_collection_type
 * @property bool|null $tab_product_collection_slider_loop
 * @property bool|null $tab_product_collection_slider_autoplay
 * @property int|null $tab_product_collection_limit
 * @property string|null $product_collection_title
 * @property int|null $product_collection_id
 * @property string|null $product_collection_type
 * @property bool|null $product_collection_slider_loop
 * @property bool|null $product_collection_slider_autoplay
 * @property int|null $product_collection_limit
 * @property string|null $category_slider_title
 * @property bool|null $category_slider_loop
 * @property bool|null $category_slider_autoplay
 * @property string|null $category_slider_ids
 * @property string|null $brand_slider_title
 * @property bool|null $brand_slider_loop
 * @property bool|null $brand_slider_autoplay
 * @property string|null $brand_slider_ids
 * @property string|null $three_c_banner_link1
 * @property string|null $three_c_banner_image1
 * @property string|null $three_c_banner_link2
 * @property string|null $three_c_banner_image2
 * @property string|null $three_c_banner_link3
 * @property string|null $three_c_banner_image3
 * @property string|null $two_c_banner_link1
 * @property string|null $two_c_banner_image1
 * @property string|null $two_c_banner_link2
 * @property string|null $two_c_banner_image2
 * @property string|null $one_c_banner_link1
 * @property string|null $one_c_banner_image1
 * @property string|null $text_title
 * @property string|null $text_content
 * @property string|null $slider_images
 * @property string|null $slider_links
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Page $page
 */
class PageWidget extends Model
{
    use HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'page_widgets';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'page_id',
        'order',
        'product_category_title',
        'product_category_id',
        'product_category_type',
        'product_category_slider_loop',
        'product_category_slider_autoplay',
        'product_category_limit',
        'tab_product_collection_id',
        'tab_product_collection_type',
        'tab_product_collection_slider_loop',
        'tab_product_collection_slider_autoplay',
        'tab_product_collection_limit',
        'product_collection_title',
        'product_collection_id',
        'product_collection_type',
        'product_collection_slider_loop',
        'product_collection_slider_autoplay',
        'product_collection_limit',
        'category_slider_title',
        'category_slider_loop',
        'category_slider_autoplay',
        'category_slider_ids',
        'brand_slider_title',
        'brand_slider_loop',
        'brand_slider_autoplay',
        'brand_slider_ids',
        'three_c_banner_link1',
        'three_c_banner_image1',
        'three_c_banner_link2',
        'three_c_banner_image2',
        'three_c_banner_link3',
        'three_c_banner_image3',
        'two_c_banner_link1',
        'two_c_banner_image1',
        'two_c_banner_link2',
        'two_c_banner_image2',
        'one_c_banner_link1',
        'one_c_banner_image1',
        'text_title',
        'text_content',
        'slider_images',
        'slider_links',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'page_id' => 'integer',
            'order' => 'integer',
            'product_category_id' => 'integer',
            'product_category_slider_loop' => 'boolean',
            'product_category_slider_autoplay' => 'boolean',
            'product_category_limit' => 'integer',
            'tab_product_collection_id' => 'integer',
            'tab_product_collection_slider_loop' => 'boolean',
            'tab_product_collection_slider_autoplay' => 'boolean',
            'tab_product_collection_limit' => 'integer',
            'product_collection_id' => 'integer',
            'product_collection_slider_loop' => 'boolean',
            'product_collection_slider_autoplay' => 'boolean',
            'product_collection_limit' => 'integer',
            'category_slider_loop' => 'boolean',
            'category_slider_autoplay' => 'boolean',
            'brand_slider_loop' => 'boolean',
            'brand_slider_autoplay' => 'boolean',
        ];
    }

    /**
     * Get the page that owns this widget.
     *
     * @return BelongsTo<Page, self>
     */
    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }
}

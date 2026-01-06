<?php

declare(strict_types=1);

namespace Modules\Ecommerce\Models;

use App\Models\Customer;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * ProductReview Model
 *
 * Represents a product review/rating by a customer.
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $customer_id
 * @property string|null $customer_name
 * @property int $rating
 * @property string|null $review
 * @property bool $approved
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read Product $product
 * @property-read Customer|null $customer
 *
 * @method static Builder|ProductReview approved()
 * @method static Builder|ProductReview pending()
 */
class ProductReview extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'customer_id',
        'customer_name',
        'rating',
        'review',
        'approved',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'product_id' => 'integer',
            'customer_id' => 'integer',
            'rating' => 'integer',
            'approved' => 'boolean',
        ];
    }

    /**
     * Get the product for this review.
     *
     * @return BelongsTo<Product, self>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the customer who wrote this review.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Scope a query to only include approved reviews.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approved', true);
    }

    /**
     * Scope a query to only include pending reviews.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('approved', false);
    }
}

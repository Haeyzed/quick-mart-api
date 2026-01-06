<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * DiscountPlanDiscount Model (Pivot)
 *
 * Represents the relationship between discount plans and discounts.
 *
 * @property int $id
 * @property int $discount_plan_id
 * @property int $discount_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read DiscountPlan $discountPlan
 * @property-read Discount $discount
 */
class DiscountPlanDiscount extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'discount_plan_id',
        'discount_id',
    ];

    /**
     * Get the discount plan.
     *
     * @return BelongsTo<DiscountPlan, self>
     */
    public function discountPlan(): BelongsTo
    {
        return $this->belongsTo(DiscountPlan::class);
    }

    /**
     * Get the discount.
     *
     * @return BelongsTo<Discount, self>
     */
    public function discount(): BelongsTo
    {
        return $this->belongsTo(Discount::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'discount_plan_id' => 'integer',
            'discount_id' => 'integer',
        ];
    }
}


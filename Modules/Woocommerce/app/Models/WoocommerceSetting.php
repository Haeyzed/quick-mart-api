<?php

declare(strict_types=1);

namespace Modules\Woocommerce\Models;

use App\Models\Biller;
use App\Models\CustomerGroup;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * WoocommerceSetting Model
 *
 * Represents WooCommerce integration configuration settings.
 *
 * @property int $id
 * @property string $woocomerce_app_url
 * @property string $woocomerce_consumer_key
 * @property string $woocomerce_consumer_secret
 * @property string|null $default_tax_class
 * @property string|null $product_tax_type
 * @property bool $manage_stock
 * @property string|null $stock_status
 * @property string|null $product_status
 * @property int|null $customer_group_id
 * @property int|null $warehouse_id
 * @property int|null $biller_id
 * @property string|null $order_status_pending
 * @property string|null $order_status_processing
 * @property string|null $order_status_on_hold
 * @property string|null $order_status_completed
 * @property string|null $order_status_draft
 * @property string|null $webhook_secret_order_created
 * @property string|null $webhook_secret_order_updated
 * @property string|null $webhook_secret_order_deleted
 * @property string|null $webhook_secret_order_restored
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @property-read CustomerGroup|null $customerGroup
 * @property-read Warehouse|null $warehouse
 * @property-read Biller|null $biller
 */
class WoocommerceSetting extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'woocomerce_app_url',
        'woocomerce_consumer_key',
        'woocomerce_consumer_secret',
        'default_tax_class',
        'product_tax_type',
        'manage_stock',
        'stock_status',
        'product_status',
        'customer_group_id',
        'warehouse_id',
        'biller_id',
        'order_status_pending',
        'order_status_processing',
        'order_status_on_hold',
        'order_status_completed',
        'order_status_draft',
        'webhook_secret_order_created',
        'webhook_secret_order_updated',
        'webhook_secret_order_deleted',
        'webhook_secret_order_restored',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'manage_stock' => 'boolean',
            'customer_group_id' => 'integer',
            'warehouse_id' => 'integer',
            'biller_id' => 'integer',
        ];
    }

    /**
     * Get the customer group for WooCommerce orders.
     *
     * @return BelongsTo<CustomerGroup, self>
     */
    public function customerGroup(): BelongsTo
    {
        return $this->belongsTo(CustomerGroup::class);
    }

    /**
     * Get the warehouse for WooCommerce orders.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the biller for WooCommerce orders.
     *
     * @return BelongsTo<Biller, self>
     */
    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * PosSetting Model
 * 
 * Represents POS (Point of Sale) system settings.
 *
 * @property int $id
 * @property int|null $customer_id
 * @property int|null $warehouse_id
 * @property int|null $biller_id
 * @property int $product_number
 * @property string|null $stripe_public_key
 * @property string|null $stripe_secret_key
 * @property string|null $paypal_live_api_username
 * @property string|null $paypal_live_api_password
 * @property string|null $paypal_live_api_secret
 * @property string|null $payment_options
 * @property bool $show_print_invoice
 * @property string|null $invoice_option
 * @property string|null $thermal_invoice_size
 * @property bool $keybord_active
 * @property bool $is_table
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer|null $customer
 * @property-read Warehouse|null $warehouse
 * @property-read Biller|null $biller
 * @property int $send_sms
 * @property int $cash_register
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereBillerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereCashRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereInvoiceOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereIsTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereKeybordActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting wherePaymentOptions($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting wherePaypalLiveApiPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting wherePaypalLiveApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting wherePaypalLiveApiUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereProductNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereSendSms($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereShowPrintInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereStripePublicKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereStripeSecretKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereThermalInvoiceSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PosSetting whereWarehouseId($value)
 * @mixin \Eloquent
 */
class PosSetting extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pos_setting';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'customer_id',
        'warehouse_id',
        'biller_id',
        'product_number',
        'stripe_public_key',
        'stripe_secret_key',
        'paypal_live_api_username',
        'paypal_live_api_password',
        'paypal_live_api_secret',
        'payment_options',
        'show_print_invoice',
        'invoice_option',
        'thermal_invoice_size',
        'keybord_active',
        'is_table',
        'send_sms',
        'cash_register',
    ];

    /**
     * Get the default customer for POS.
     *
     * @return BelongsTo<Customer, self>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Get the default warehouse for POS.
     *
     * @return BelongsTo<Warehouse, self>
     */
    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    /**
     * Get the default biller for POS.
     *
     * @return BelongsTo<Biller, self>
     */
    public function biller(): BelongsTo
    {
        return $this->belongsTo(Biller::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'customer_id' => 'integer',
            'warehouse_id' => 'integer',
            'biller_id' => 'integer',
            'product_number' => 'integer',
            'show_print_invoice' => 'boolean',
            'keybord_active' => 'boolean',
            'is_table' => 'boolean',
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * InvoiceSetting Model
 *
 * Represents invoice template and formatting settings.
 *
 * @property int $id
 * @property string $template_name
 * @property string $invoice_name
 * @property string|null $invoice_logo
 * @property string|null $file_type
 * @property string|null $prefix
 * @property int $number_of_digit
 * @property string|null $numbering_type
 * @property int $start_number
 * @property int $status
 * @property string|null $header_text
 * @property string|null $header_title
 * @property string|null $footer_text
 * @property string|null $footer_title
 * @property bool $show_barcode
 * @property bool $show_qr_code
 * @property bool $is_default
 * @property bool $show_customer_details
 * @property bool $show_shipping_details
 * @property bool $show_payment_info
 * @property bool $show_discount
 * @property bool $show_tax_info
 * @property bool $show_description
 * @property bool $show_billing_info
 * @property string|null $show_column
 * @property string|null $preview_invoice
 * @property bool $show_in_words
 * @property string|null $company_logo
 * @property int|null $logo_height
 * @property int|null $logo_width
 * @property string|null $primary_color
 * @property string|null $text_color
 * @property string|null $secondary_color
 * @property string|null $size
 * @property string|null $invoice_date_format
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $creator
 * @property-read User|null $updater
 *
 * @method static Builder|InvoiceSetting active()
 * @method static Builder|InvoiceSetting default()
 */
class InvoiceSetting extends Model implements AuditableContract
{
    use Auditable, HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'invoice_settings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'template_name',
        'invoice_name',
        'invoice_logo',
        'file_type',
        'prefix',
        'number_of_digit',
        'numbering_type',
        'start_number',
        'status',
        'header_text',
        'header_title',
        'footer_text',
        'footer_title',
        'show_barcode',
        'show_qr_code',
        'is_default',
        'show_customer_details',
        'show_shipping_details',
        'show_payment_info',
        'show_discount',
        'show_tax_info',
        'show_description',
        'show_billing_info',
        'show_column',
        'preview_invoice',
        'show_in_words',
        'company_logo',
        'logo_height',
        'logo_width',
        'primary_color',
        'text_color',
        'secondary_color',
        'size',
        'invoice_date_format',
        'created_by',
        'updated_by',
    ];

    /**
     * Get the active invoice setting.
     */
    public static function activeSetting(): ?self
    {
        $settings = static::where('status', 1)->first();
        if ($settings === null) {
            $settings = static::where('is_default', true)->first();
        }

        return $settings;
    }

    /**
     * Boot the model.
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (InvoiceSetting $setting): void {
            if (auth()->check()) {
                $setting->created_by = auth()->id();
            }
        });

        static::updating(function (InvoiceSetting $setting): void {
            if (auth()->check()) {
                $setting->updated_by = auth()->id();
            }
        });
    }

    /**
     * Get the user who created this invoice setting.
     *
     * @return BelongsTo<User, self>
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who updated this invoice setting.
     *
     * @return BelongsTo<User, self>
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Scope a query to only include active invoice settings.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 1);
    }

    /**
     * Scope a query to only include default invoice setting.
     */
    public function scopeDefault(Builder $query): Builder
    {
        return $query->where('is_default', true);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'number_of_digit' => 'integer',
            'start_number' => 'integer',
            'status' => 'integer',
            'show_barcode' => 'boolean',
            'show_qr_code' => 'boolean',
            'is_default' => 'boolean',
            'show_customer_details' => 'boolean',
            'show_shipping_details' => 'boolean',
            'show_payment_info' => 'boolean',
            'show_discount' => 'boolean',
            'show_tax_info' => 'boolean',
            'show_description' => 'boolean',
            'show_billing_info' => 'boolean',
            'show_in_words' => 'boolean',
            'logo_height' => 'integer',
            'logo_width' => 'integer',
            'created_by' => 'integer',
            'updated_by' => 'integer',
        ];
    }
}

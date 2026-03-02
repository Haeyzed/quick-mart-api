<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\FilterableByDates;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * Class InvoiceSetting
 *
 * Represents invoice template and formatting settings. Handles the underlying data
 * structure, relationships, and specific query scopes for invoice setting entities.
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
 * @property int|null $last_invoice_number
 * @property string|null $extra
 *
 * @method static Builder|InvoiceSetting newModelQuery()
 * @method static Builder|InvoiceSetting newQuery()
 * @method static Builder|InvoiceSetting query()
 * @method static Builder|InvoiceSetting active()
 * @method static Builder|InvoiceSetting default()
 * @method static Builder|InvoiceSetting filter(array $filters)
 *
 * @property-read \App\Models\User|null $creator
 * @property-read \App\Models\User|null $updater
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 *
 * @method static Builder<static>|InvoiceSetting customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting last30Days(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting last7Days(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting lastQuarter(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting lastYear(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting monthToDate(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting quarterToDate(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting today(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting whereCompanyLogo($value)
 * @method static Builder<static>|InvoiceSetting whereCreatedAt($value)
 * @method static Builder<static>|InvoiceSetting whereCreatedBy($value)
 * @method static Builder<static>|InvoiceSetting whereExtra($value)
 * @method static Builder<static>|InvoiceSetting whereFileType($value)
 * @method static Builder<static>|InvoiceSetting whereFooterText($value)
 * @method static Builder<static>|InvoiceSetting whereFooterTitle($value)
 * @method static Builder<static>|InvoiceSetting whereHeaderText($value)
 * @method static Builder<static>|InvoiceSetting whereHeaderTitle($value)
 * @method static Builder<static>|InvoiceSetting whereId($value)
 * @method static Builder<static>|InvoiceSetting whereInvoiceDateFormat($value)
 * @method static Builder<static>|InvoiceSetting whereInvoiceLogo($value)
 * @method static Builder<static>|InvoiceSetting whereInvoiceName($value)
 * @method static Builder<static>|InvoiceSetting whereIsDefault($value)
 * @method static Builder<static>|InvoiceSetting whereLastInvoiceNumber($value)
 * @method static Builder<static>|InvoiceSetting whereLogoHeight($value)
 * @method static Builder<static>|InvoiceSetting whereLogoWidth($value)
 * @method static Builder<static>|InvoiceSetting whereNumberOfDigit($value)
 * @method static Builder<static>|InvoiceSetting whereNumberingType($value)
 * @method static Builder<static>|InvoiceSetting wherePrefix($value)
 * @method static Builder<static>|InvoiceSetting wherePreviewInvoice($value)
 * @method static Builder<static>|InvoiceSetting wherePrimaryColor($value)
 * @method static Builder<static>|InvoiceSetting whereSecondaryColor($value)
 * @method static Builder<static>|InvoiceSetting whereShowColumn($value)
 * @method static Builder<static>|InvoiceSetting whereSize($value)
 * @method static Builder<static>|InvoiceSetting whereStartNumber($value)
 * @method static Builder<static>|InvoiceSetting whereStatus($value)
 * @method static Builder<static>|InvoiceSetting whereTemplateName($value)
 * @method static Builder<static>|InvoiceSetting whereTextColor($value)
 * @method static Builder<static>|InvoiceSetting whereUpdatedAt($value)
 * @method static Builder<static>|InvoiceSetting whereUpdatedBy($value)
 * @method static Builder<static>|InvoiceSetting yearToDate(string $column = 'created_at')
 * @method static Builder<static>|InvoiceSetting yesterday(string $column = 'current_at')
 *
 * @mixin \Eloquent
 */
class InvoiceSetting extends Model implements AuditableContract
{
    use Auditable, FilterableByDates, HasFactory;

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
     * The attributes that should be cast to native types.
     *
     * @var array<string, string>
     */
    protected $casts = [
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

    /**
     * Scope a query to apply dynamic filters.
     *
     * @param  Builder  $query  The Eloquent query builder instance.
     * @param  array<string, mixed>  $filters  An associative array of requested filters.
     * @return Builder The modified query builder instance.
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        return $query
            ->when(
                isset($filters['status']),
                fn (Builder $q) => $q->where('status', $filters['status'])
            )
            ->when(
                isset($filters['is_default']),
                fn (Builder $q) => $q->default()
            )
            ->when(
                ! empty($filters['search']),
                function (Builder $q) use ($filters) {
                    $term = "%{$filters['search']}%";
                    $q->where('template_name', 'like', $term)
                        ->orWhere('invoice_name', 'like', $term);
                }
            )
            ->customRange(
                ! empty($filters['start_date']) ? $filters['start_date'] : null,
                ! empty($filters['end_date']) ? $filters['end_date'] : null,
            );
    }

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
}

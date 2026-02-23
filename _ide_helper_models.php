<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * Account Model
 * 
 * Represents a financial account in the accounting system.
 *
 * @property int $id
 * @property string $account_no
 * @property string $name
 * @property float $initial_balance
 * @property float $total_balance
 * @property string|null $note
 * @property bool $is_default
 * @property bool $is_active
 * @property string|null $code
 * @property string|null $type
 * @property int|null $parent_account_id
 * @property bool|null $is_payment
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Account|null $parent
 * @property-read Collection<int, Account> $children
 * @property-read Collection<int, Payment> $payments
 * @property-read Collection<int, MoneyTransfer> $fromTransfers
 * @property-read Collection<int, MoneyTransfer> $toTransfers
 * @method static Builder|Account active()
 * @method static Builder|Account default()
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $children_count
 * @property-read int|null $from_transfers_count
 * @property-read int|null $payments_count
 * @property-read int|null $to_transfers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereAccountNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereInitialBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereIsPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereParentAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereTotalBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Account whereUpdatedAt($value)
 */
	class Account extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ActivityLog Model
 * 
 * Represents an activity log entry for user actions.
 *
 * @property int $id
 * @property Carbon $date
 * @property int $user_id
 * @property string $action
 * @property string|null $reference_no
 * @property string|null $item_description
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereItemDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ActivityLog whereUserId($value)
 */
	class ActivityLog extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Adjustment Model
 * 
 * Represents a stock quantity adjustment for a warehouse.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $warehouse_id
 * @property string|null $document
 * @property float $total_qty
 * @property int $item
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Warehouse $warehouse
 * @property-read Collection<int, ProductAdjustment> $productAdjustments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_adjustments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Adjustment whereWarehouseId($value)
 */
	class Adjustment extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Attendance Model
 * 
 * Represents an attendance record for an employee.
 *
 * @property int $id
 * @property Carbon $date
 * @property int $employee_id
 * @property int $user_id
 * @property string|null $checkin
 * @property string|null $checkout
 * @property string $status
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read User $user
 * @method static Builder|Attendance present()
 * @method static Builder|Attendance absent()
 * @method static Builder|Attendance late()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCheckout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Attendance whereUserId($value)
 */
	class Attendance extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Barcode Model
 * 
 * Represents a barcode format configuration.
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Barcode default()
 * @property numeric|null $width
 * @property numeric|null $height
 * @property numeric|null $paper_width
 * @property numeric|null $paper_height
 * @property numeric|null $top_margin
 * @property numeric|null $left_margin
 * @property numeric|null $row_distance
 * @property numeric|null $col_distance
 * @property int|null $stickers_in_one_row
 * @property int $is_continuous
 * @property int|null $stickers_in_one_sheet
 * @property int|null $is_custom
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereColDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereIsContinuous($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereIsCustom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereLeftMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode wherePaperHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode wherePaperWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereRowDistance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereStickersInOneRow($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereStickersInOneSheet($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereTopMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barcode whereWidth($value)
 */
	class Barcode extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Biller
 * 
 * Represents a biller entity.
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $phone_number
 * @property string|null $company_name
 * @property string|null $vat_number
 * @property string|null $address
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property string|null $postal_code
 * @property string|null $image
 * @property string|null $image_url
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Biller newModelQuery()
 * @method static Builder|Biller newQuery()
 * @method static Builder|Biller query()
 * @method static Builder|Biller active()
 * @method static Builder|Biller filter(array $filters)
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read City|null $city
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller whereVatNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Biller yesterday(string $column = 'current_at')
 */
	class Biller extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Brand
 * 
 * Represents a product brand within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for brand entities.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $page_title
 * @property string|null $image
 * @property string|null $image_url
 * @property bool $is_active
 * @property Carbon|null $start_date
 * @property Carbon|null $end_date
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Brand newModelQuery()
 * @method static Builder|Brand newQuery()
 * @method static Builder|Brand query()
 * @method static Builder|Brand active()
 * @method static Builder|Brand filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Brand yesterday(string $column = 'current_at')
 */
	class Brand extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * CashRegister Model
 * 
 * Represents a cash register session for a user in a warehouse.
 *
 * @property int $id
 * @property float $cash_in_hand
 * @property int $user_id
 * @property int $warehouse_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Warehouse $warehouse
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Payment> $payments
 * @method static Builder|CashRegister open()
 * @method static Builder|CashRegister closed()
 * @property float|null $closing_balance
 * @property float|null $actual_cash
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $payments_count
 * @property-read int|null $sales_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereActualCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereCashInHand($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereClosingBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CashRegister whereWarehouseId($value)
 */
	class CashRegister extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Category
 * 
 * Represents a product category within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for category entities.
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string|null $short_description
 * @property string|null $page_title
 * @property string|null $image
 * @property string|null $image_url
 * @property string|null $icon
 * @property string|null $icon_url
 * @property int|null $parent_id
 * @property bool $is_active
 * @property bool $featured
 * @property bool $is_sync_disable
 * @property int|null $woocommerce_category_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category query()
 * @method static Builder|Category active()
 * @method static Builder|Category featured()
 * @method static Builder|Category syncDisabled()
 * @method static Builder|Category filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Category> $childrenRecursive
 * @property-read int|null $children_recursive_count
 * @property-read Category|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereIsSyncDisable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category wherePageTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category whereWoocommerceCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Category yesterday(string $column = 'current_at')
 */
	class Category extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Challan Model
 * 
 * Represents a challan (delivery receipt) for courier services.
 *
 * @property int $id
 * @property string $reference_no
 * @property int|null $courier_id
 * @property string $status
 * @property string|null $packing_slip_list
 * @property string|null $amount_list
 * @property string|null $cash_list
 * @property string|null $cheque_list
 * @property string|null $online_payment_list
 * @property string|null $delivery_charge_list
 * @property string|null $status_list
 * @property Carbon|null $closing_date
 * @property int|null $created_by_id
 * @property int|null $closed_by_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Courier|null $courier
 * @property-read User|null $createdBy
 * @property-read User|null $closedBy
 * @method static Builder|Challan open()
 * @method static Builder|Challan closed()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereAmountList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereCashList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereChequeList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereClosedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereClosingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereCourierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereDeliveryChargeList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereOnlinePaymentList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan wherePackingSlipList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereStatusList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challan whereUpdatedAt($value)
 */
	class Challan extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class City
 * 
 * Represents a city from World reference data. Extends Nnjeim\World City.
 *
 * @property int $id
 * @property int $country_id
 * @property int $state_id
 * @property string $name
 * @property string $country_code
 * @property string|null $state_code
 * @property string|null $latitude
 * @property string|null $longitude
 * @method static Builder|City newModelQuery()
 * @method static Builder|City newQuery()
 * @method static Builder|City query()
 * @method static Builder|City filter(array $filters)
 * @property-read \App\Models\Country|null $country
 * @property-read \App\Models\State|null $state
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereStateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|City yesterday(string $column = 'current_at')
 */
	class City extends \Eloquent {}
}

namespace App\Models{
/**
 * Class Country
 * 
 * Represents a country from World reference data. Extends Nnjeim\World Country.
 *
 * @property int $id
 * @property string $iso2
 * @property string $name
 * @property int $status
 * @property string|null $phone_code
 * @property string|null $iso3
 * @property string|null $region
 * @property string|null $subregion
 * @property string|null $native
 * @property string|null $latitude
 * @property string|null $longitude
 * @property string|null $emoji
 * @property string|null $emojiU
 * @method static Builder|Country newModelQuery()
 * @method static Builder|Country newQuery()
 * @method static Builder|Country query()
 * @method static Builder|Country filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\City> $cities
 * @property-read int|null $cities_count
 * @property-read \App\Models\Currency|null $currency
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\State> $states
 * @property-read int|null $states_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Timezone> $timezones
 * @property-read int|null $timezones_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereEmoji($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereEmojiU($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereIso2($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereIso3($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereNative($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country wherePhoneCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country whereSubregion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Country yesterday(string $column = 'current_at')
 */
	class Country extends \Eloquent {}
}

namespace App\Models{
/**
 * Coupon Model
 * 
 * Represents a discount coupon code.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property float $amount
 * @property float|null $minimum_amount
 * @property int|null $user_id
 * @property int $quantity
 * @property int $used
 * @property Carbon|null $expired_date
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User|null $user
 * @property-read Collection<int, Sale> $sales
 * @method static Builder|Coupon active()
 * @method static Builder|Coupon valid()
 * @method static Builder|Coupon expired()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $sales_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereExpiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereMinimumAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Coupon whereUserId($value)
 */
	class Coupon extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Courier Model
 * 
 * Represents a courier/delivery service provider.
 *
 * @property int $id
 * @property string $name
 * @property string|null $phone_number
 * @property string|null $address
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Delivery> $deliveries
 * @method static Builder|Courier active()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $deliveries_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Courier withoutTrashed()
 */
	class Courier extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Currency
 * 
 * Represents a currency from World reference data. Extends Nnjeim\World Currency.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @property string $code
 * @property int $precision
 * @property string $symbol
 * @property string $symbol_native
 * @property bool $symbol_first
 * @property string $decimal_mark
 * @property string $thousands_separator
 * @method static Builder|Currency newModelQuery()
 * @method static Builder|Currency newQuery()
 * @method static Builder|Currency query()
 * @method static Builder|Currency filter(array $filters)
 * @property-read \App\Models\Country|null $country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereDecimalMark($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency wherePrecision($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereSymbol($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereSymbolFirst($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereSymbolNative($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency whereThousandsSeparator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Currency yesterday(string $column = 'current_at')
 */
	class Currency extends \Eloquent {}
}

namespace App\Models{
/**
 * CustomField Model
 * 
 * Represents a custom field that can be added to various entities.
 *
 * @property int $id
 * @property string $belongs_to
 * @property string $name
 * @property string $type
 * @property string|null $default_value
 * @property string|null $option_value
 * @property string|null $grid_value
 * @property bool $is_table
 * @property bool $is_invoice
 * @property bool $is_required
 * @property bool $is_admin
 * @property bool $is_disable
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereBelongsTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereDefaultValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereGridValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsDisable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereIsTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereOptionValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomField whereUpdatedAt($value)
 */
	class CustomField extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Customer Model
 * 
 * Represents a customer in the system.
 *
 * @property int $id
 * @property int|null $customer_group_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $company_name
 * @property string|null $email
 * @property string $type
 * @property string|null $phone_number
 * @property string|null $wa_number
 * @property string|null $tax_no
 * @property string|null $address
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property string|null $postal_code
 * @property float $opening_balance
 * @property float $credit_limit
 * @property float $points
 * @property float $deposit
 * @property int|null $pay_term_no
 * @property string|null $pay_term_period
 * @property float $expense
 * @property string|null $wishlist
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read City|null $city
 * @property-read CustomerGroup|null $customerGroup
 * @property-read User|null $user
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, DiscountPlan> $discountPlans
 * @property-read Collection<int, RewardPoint> $rewardPoints
 * @property-read Collection<int, Deposit> $deposits
 * @method static Builder|Customer active()
 * @method static Builder|Customer filter(array $filters)
 * @property string|null $ecom
 * @property string $dsf
 * @property string|null $arabic_name
 * @property string|null $admin
 * @property string|null $franchise_location
 * @property string $customer_type
 * @property string $customer_assigned_to
 * @property string $assigned
 * @property string $aaaaaaaa
 * @property string|null $district
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $deposits_count
 * @property-read int|null $discount_plans_count
 * @property-read int|null $reward_points_count
 * @property-read int|null $sales_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAaaaaaaa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAdmin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereArabicName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereAssigned($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCreditLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCustomerAssignedTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCustomerGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereCustomerType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDeposit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDistrict($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereDsf($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereEcom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereFranchiseLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePayTermNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePayTermPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereTaxNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereWaNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer whereWishlist($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Customer withoutTrashed()
 */
	class Customer extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class CustomerGroup
 * 
 * Represents a customer group within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for customer group entities.
 *
 * @property int $id
 * @property string $name
 * @property float $percentage
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Customer> $customers
 * @method static Builder|CustomerGroup newModelQuery()
 * @method static Builder|CustomerGroup newQuery()
 * @method static Builder|CustomerGroup query()
 * @method static Builder|CustomerGroup active()
 * @method static Builder|CustomerGroup filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $customers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup wherePercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CustomerGroup yesterday(string $column = 'current_at')
 */
	class CustomerGroup extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Delivery Model
 * 
 * Represents a delivery record for a sale.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $sale_id
 * @property string|null $packing_slip_ids
 * @property int $user_id
 * @property string|null $address
 * @property int|null $courier_id
 * @property string|null $delivered_by
 * @property string|null $recieved_by
 * @property string|null $file
 * @property string $status
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sale $sale
 * @property-read User $user
 * @property-read Courier|null $courier
 * @method static Builder|Delivery pending()
 * @method static Builder|Delivery delivered()
 * @method static Builder|Delivery cancelled()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereCourierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereDeliveredBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery wherePackingSlipIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereRecievedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Delivery whereUserId($value)
 */
	class Delivery extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Department
 * 
 * Represents a department within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for department entities.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Department newModelQuery()
 * @method static Builder|Department newQuery()
 * @method static Builder|Department query()
 * @method static Builder|Department active()
 * @method static Builder|Department filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Employee> $employees
 * @property-read int|null $employees_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Department yesterday(string $column = 'current_at')
 */
	class Department extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Deposit Model
 * 
 * Represents a customer deposit transaction.
 *
 * @property int $id
 * @property float $amount
 * @property int $customer_id
 * @property int $user_id
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Deposit whereUserId($value)
 */
	class Deposit extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Designation Model
 * 
 * Represents a job designation/position in the organization.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Employee> $employees
 * @method static Builder|Designation active()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $employees_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Designation withoutTrashed()
 */
	class Designation extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Discount Model
 * 
 * Represents a discount rule that can be applied to products.
 *
 * @property int $id
 * @property string $name
 * @property string $applicable_for
 * @property array<int>|null $product_list
 * @property Carbon|null $valid_from
 * @property Carbon|null $valid_till
 * @property string $type
 * @property float $value
 * @property int|null $minimum_qty
 * @property int|null $maximum_qty
 * @property array<string>|null $days
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, DiscountPlan> $discountPlans
 * @method static Builder|Discount active()
 * @method static Builder|Discount valid()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $discount_plans_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereApplicableFor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereMaximumQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereMinimumQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereProductList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereValidFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereValidTill($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount whereValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Discount withoutTrashed()
 */
	class Discount extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * DiscountPlan Model
 * 
 * Represents a discount plan that groups multiple discounts and can be assigned to customers.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 * @property string $type
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Customer> $customers
 * @property-read Collection<int, Discount> $discounts
 * @method static Builder|DiscountPlan active()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $customers_count
 * @property-read int|null $discounts_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlan withoutTrashed()
 */
	class DiscountPlan extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * DiscountPlanCustomer Model (Pivot)
 * 
 * Represents the relationship between discount plans and customers.
 *
 * @property int $id
 * @property int $discount_plan_id
 * @property int $customer_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DiscountPlan $discountPlan
 * @property-read Customer $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereDiscountPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanCustomer whereUpdatedAt($value)
 */
	class DiscountPlanCustomer extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
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
 * @property-read DiscountPlan $discountPlan
 * @property-read Discount $discount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount whereDiscountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount whereDiscountPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiscountPlanDiscount whereUpdatedAt($value)
 */
	class DiscountPlanDiscount extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Employee Model
 * 
 * Represents an employee in the organization.
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property int $department_id
 * @property int $designation_id
 * @property int $shift_id
 * @property float $basic_salary
 * @property string|null $email
 * @property string|null $phone_number
 * @property int|null $user_id
 * @property string $staff_id
 * @property string|null $address
 * @property string|null $city
 * @property string|null $country
 * @property bool $is_active
 * @property bool $is_sale_agent
 * @property float|null $sale_commission_percent
 * @property array|null $sales_target
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Department $department
 * @property-read Designation $designation
 * @property-read Shift $shift
 * @property-read User|null $user
 * @property-read Collection<int, Payroll> $payrolls
 * @property-read Collection<int, Attendance> $attendances
 * @property-read Collection<int, Leave> $leaves
 * @property-read Collection<int, Overtime> $overtimes
 * @property-read Collection<int, EmployeeTransaction> $transactions
 * @method static Builder|Employee active()
 * @method static Builder|Employee saleAgents()
 * @method static Builder|Employee filter(array $filters)
 * @property string|null $deleted_at
 * @property-read int|null $attendances_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $leaves_count
 * @property-read int|null $overtimes_count
 * @property-read int|null $payrolls_count
 * @property-read int|null $transactions_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereBasicSalary($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereIsSaleAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSaleCommissionPercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereSalesTarget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereShiftId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereStaffId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Employee whereUserId($value)
 */
	class Employee extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * EmployeeTransaction Model
 * 
 * Represents a financial transaction for an employee (advance, loan, etc.).
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $date
 * @property float $amount
 * @property string $type
 * @property string|null $description
 * @property int $created_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read User $creator
 * @method static Builder|EmployeeTransaction advance()
 * @method static Builder|EmployeeTransaction loan()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeTransaction newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeTransaction newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EmployeeTransaction query()
 */
	class EmployeeTransaction extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Expense Model
 * 
 * Represents an expense transaction.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $expense_category_id
 * @property int $warehouse_id
 * @property int|null $account_id
 * @property int $user_id
 * @property int|null $cash_register_id
 * @property int|null $employee_id
 * @property string $type
 * @property float $amount
 * @property string|null $note
 * @property string|null $document
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ExpenseCategory $expenseCategory
 * @property-read Warehouse $warehouse
 * @property-read Account|null $account
 * @property-read User $user
 * @property-read CashRegister|null $cashRegister
 * @property-read Employee|null $employee
 * @property int|null $boutique_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereBoutiqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCashRegisterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereExpenseCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Expense whereWarehouseId($value)
 */
	class Expense extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ExpenseCategory Model
 * 
 * Represents a category for expenses.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Expense> $expenses
 * @method static Builder|ExpenseCategory active()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $expenses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExpenseCategory withoutTrashed()
 */
	class ExpenseCategory extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ExternalService Model
 * 
 * Represents an external service integration configuration.
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string|null $details
 * @property bool $active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|ExternalService active()
 * @property string|null $module_status
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereModuleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ExternalService whereUpdatedAt($value)
 */
	class ExternalService extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * GeneralSetting Model
 * 
 * Represents the general application settings (singleton pattern).
 *
 * @property int $id
 * @property string|null $site_title
 * @property string|null $site_logo
 * @property string|null $site_logo_url
 * @property bool $is_rtl
 * @property string|null $currency
 * @property string|null $currency_position
 * @property string|null $staff_access
 * @property bool $without_stock
 * @property bool $is_packing_slip
 * @property string|null $date_format
 * @property string|null $theme
 * @property string|null $modules
 * @property string|null $developed_by
 * @property string|null $phone
 * @property string|null $email
 * @property int|null $free_trial_limit
 * @property int|null $package_id
 * @property string|null $invoice_format
 * @property int|null $decimal
 * @property string|null $state
 * @property Carbon|null $expiry_date
 * @property string|null $expiry_type
 * @property int|null $expiry_value
 * @property string|null $subscription_type
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property string|null $active_payment_gateway
 * @property string|null $stripe_public_key
 * @property string|null $stripe_secret_key
 * @property string|null $paypal_client_id
 * @property string|null $paypal_client_secret
 * @property string|null $razorpay_number
 * @property string|null $razorpay_key
 * @property string|null $razorpay_secret
 * @property bool $is_zatca
 * @property string|null $company_name
 * @property string|null $vat_registration_number
 * @property string|null $dedicated_ip
 * @property string|null $paystack_public_key
 * @property string|null $paystack_secret_key
 * @property string|null $paydunya_master_key
 * @property string|null $paydunya_public_key
 * @property string|null $paydunya_secret_key
 * @property string|null $paydunya_token
 * @property string|null $ssl_store_id
 * @property string|null $ssl_store_password
 * @property string|null $app_key
 * @property bool|null $show_products_details_in_sales_table
 * @property bool|null $show_products_details_in_purchase_table
 * @property string|null $timezone
 * @property string|null $font_css
 * @property string|null $pos_css
 * @property string|null $auth_css
 * @property string|null $custom_css
 * @property bool|null $disable_signup
 * @property bool|null $disable_forgot_password
 * @property string|null $maintenance_allowed_ips
 * @property string|null $favicon
 * @property string|null $favicon_url
 * @property int|null $expiry_alert_days
 * @property string|null $margin_type
 * @property string|null $storage_provider
 * @property string|null $google_client_id
 * @property string|null $google_client_secret
 * @property string|null $google_redirect_url
 * @property bool $google_login_enabled
 * @property string|null $facebook_client_id
 * @property string|null $facebook_client_secret
 * @property string|null $facebook_redirect_url
 * @property bool $facebook_login_enabled
 * @property string|null $github_client_id
 * @property string|null $github_client_secret
 * @property string|null $github_redirect_url
 * @property bool $github_login_enabled
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $token
 * @property numeric $default_margin_value
 * @property string|null $cloudinary_cloud_name Cloudinary Cloud Name
 * @property string|null $cloudinary_api_key Cloudinary API Key
 * @property string|null $cloudinary_api_secret Cloudinary API Secret
 * @property string|null $cloudinary_secure_url Cloudinary Secure URL (optional)
 * @property string|null $aws_access_key_id AWS Access Key ID
 * @property string|null $aws_secret_access_key AWS Secret Access Key
 * @property string|null $aws_default_region AWS Default Region
 * @property string|null $aws_bucket AWS S3 Bucket Name
 * @property string|null $aws_url AWS S3 URL (optional)
 * @property string|null $aws_endpoint AWS S3 Endpoint (optional, for custom S3-compatible services)
 * @property bool $aws_use_path_style_endpoint Use path-style endpoint for S3
 * @property string|null $sftp_host SFTP Host
 * @property string|null $sftp_username SFTP Username
 * @property string|null $sftp_password SFTP Password
 * @property string|null $sftp_private_key SFTP Private Key (optional, for key-based authentication)
 * @property string|null $sftp_passphrase SFTP Passphrase (optional, for encrypted private keys)
 * @property int $sftp_port SFTP Port
 * @property string $sftp_root SFTP Root Directory
 * @property string|null $ftp_host FTP Host
 * @property string|null $ftp_username FTP Username
 * @property string|null $ftp_password FTP Password
 * @property int $ftp_port FTP Port
 * @property string $ftp_root FTP Root Directory
 * @property bool $ftp_passive FTP Passive Mode
 * @property bool $ftp_ssl FTP SSL/TLS
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAppKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAuthCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsAccessKeyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsBucket($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsDefaultRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsSecretAccessKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereAwsUsePathStyleEndpoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinaryApiKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinaryApiSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinaryCloudName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCloudinarySecureUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCurrencyPosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereCustomCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDecimal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDefaultMarginValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDevelopedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDisableForgotPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereDisableSignup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryAlertDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereExpiryValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookLoginEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFacebookRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFavicon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFaviconUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFontCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpPassive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpSsl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereFtpUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubLoginEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGithubRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleClientSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleLoginEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereGoogleRedirectUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereInvoiceFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereIsPackingSlip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereIsRtl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereIsZatca($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereMaintenanceAllowedIps($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereMarginType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereModules($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting wherePackageId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting wherePosCss($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPassphrase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpPrivateKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSftpUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereShowProductsDetailsInPurchaseTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereShowProductsDetailsInSalesTable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSiteLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSiteLogoUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSiteTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereStaffAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereStorageProvider($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereSubscriptionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereTheme($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereVatRegistrationNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GeneralSetting whereWithoutStock($value)
 */
	class GeneralSetting extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * GiftCard Model
 * 
 * Represents a gift card that can be used for payments.
 *
 * @property int $id
 * @property string $card_no
 * @property float $amount
 * @property float $expense
 * @property int|null $customer_id
 * @property int|null $user_id
 * @property Carbon|null $expired_date
 * @property int|null $created_by
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer|null $customer
 * @property-read User|null $user
 * @property-read User|null $creator
 * @property-read Collection<int, PaymentWithGiftCard> $payments
 * @method static Builder|GiftCard active()
 * @method static Builder|GiftCard expired()
 * @method static Builder|GiftCard notExpired()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $payments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereExpense($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereExpiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCard withoutTrashed()
 */
	class GiftCard extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * GiftCardRecharge Model
 * 
 * Represents a recharge transaction for a gift card.
 *
 * @property int $id
 * @property int $gift_card_id
 * @property float $amount
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read GiftCard $giftCard
 * @property-read User $user
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge whereGiftCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GiftCardRecharge whereUserId($value)
 */
	class GiftCardRecharge extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Holiday
 * 
 * Represents a holiday/leave request within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for holiday entities.
 *
 * @property int $id
 * @property int $user_id
 * @property Carbon $from_date
 * @property Carbon $to_date
 * @property string|null $note
 * @property bool $is_approved
 * @property bool|null $recurring
 * @property string|null $region
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @method static Builder|Holiday newModelQuery()
 * @method static Builder|Holiday newQuery()
 * @method static Builder|Holiday query()
 * @method static Builder|Holiday approved()
 * @method static Builder|Holiday pending()
 * @method static Builder|Holiday filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereFromDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereIsApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereRecurring($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereToDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holiday yesterday(string $column = 'current_at')
 */
	class Holiday extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * HrmSetting Model
 * 
 * Represents HRM (Human Resource Management) system settings.
 *
 * @property int $id
 * @property string $checkin
 * @property string $checkout
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereCheckin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereCheckout($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HrmSetting whereUpdatedAt($value)
 */
	class HrmSetting extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Income
 * 
 * Represents an income transaction.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $income_category_id
 * @property int $warehouse_id
 * @property int|null $account_id
 * @property int $user_id
 * @property int|null $cash_register_id
 * @property float $amount
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read IncomeCategory $incomeCategory
 * @property-read Warehouse $warehouse
 * @property-read Account|null $account
 * @property-read User $user
 * @property-read CashRegister|null $cashRegister
 * @method static Builder|Income filter(array $filters)
 * @property int|null $boutique_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereBoutiqueId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereCashRegisterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereIncomeCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Income yesterday(string $column = 'current_at')
 */
	class Income extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * IncomeCategory Model
 * 
 * Represents a category for income.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Income> $incomes
 * @method static Builder|IncomeCategory active()
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $incomes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|IncomeCategory withoutTrashed()
 */
	class IncomeCategory extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Installment Model
 * 
 * Represents an installment payment in an installment plan.
 *
 * @property int $id
 * @property int $installment_plan_id
 * @property string $status
 * @property Carbon|null $payment_date
 * @property float $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read InstallmentPlan $plan
 * @method static Builder|Installment paid()
 * @method static Builder|Installment pending()
 * @method static Builder|Installment overdue()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereInstallmentPlanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment wherePaymentDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Installment whereUpdatedAt($value)
 */
	class Installment extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * InstallmentPlan Model
 * 
 * Represents an installment payment plan for a sale or purchase.
 *
 * @property int $id
 * @property string $reference_type
 * @property int $reference_id
 * @property string $name
 * @property float $price
 * @property float $additional_amount
 * @property float $total_amount
 * @property float $down_payment
 * @property int $months
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Model $reference
 * @property-read Collection<int, Installment> $installments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $installments_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereAdditionalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereDownPayment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereMonths($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereReferenceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereTotalAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InstallmentPlan whereUpdatedAt($value)
 */
	class InstallmentPlan extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * InvoiceSchema Model
 * 
 * Represents invoice numbering schema configuration.
 *
 * @property int $id
 * @property string $prefix
 * @property int $number_of_digit
 * @property int $start_number
 * @property int|null $last_invoice_number
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema whereLastInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema whereNumberOfDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema whereStartNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSchema whereUpdatedAt($value)
 */
	class InvoiceSchema extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
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
 * @method static Builder|InvoiceSetting active()
 * @method static Builder|InvoiceSetting default()
 * @property int|null $last_invoice_number
 * @property string|null $extra
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereCompanyLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereExtra($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereFileType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereFooterText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereFooterTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereHeaderText($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereHeaderTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereInvoiceDateFormat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereInvoiceLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereInvoiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereLastInvoiceNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereLogoHeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereLogoWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereNumberOfDigit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereNumberingType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting wherePrefix($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting wherePreviewInvoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting wherePrimaryColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereSecondaryColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereShowColumn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereStartNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereTemplateName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereTextColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceSetting whereUpdatedBy($value)
 */
	class InvoiceSetting extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Language
 * 
 * Represents a language from World reference data. Extends Nnjeim\World Language.
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $name_native
 * @property string $dir
 * @method static \Illuminate\Database\Eloquent\Builder|Language newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Language query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereDir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language whereNameNative($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Language yesterday(string $column = 'current_at')
 */
	class Language extends \Eloquent {}
}

namespace App\Models{
/**
 * Leave Model
 * 
 * Represents a leave request for an employee.
 *
 * @property int $id
 * @property int $employee_id
 * @property int $leave_types
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property int $days
 * @property string $status
 * @property int|null $approver_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read LeaveType $leaveType
 * @property-read User|null $approver
 * @method static Builder|Leave pending()
 * @method static Builder|Leave approved()
 * @method static Builder|Leave rejected()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereEndDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereLeaveTypes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereStartDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Leave yesterday(string $column = 'current_at')
 */
	class Leave extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * LeaveType Model
 * 
 * Represents a type of leave (e.g., Annual, Sick, Casual).
 *
 * @property int $id
 * @property string $name
 * @property int $annual_quota
 * @property bool $encashable
 * @property int|null $carry_forward_limit
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Leave> $leaves
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $leaves_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType whereAnnualQuota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType whereCarryForwardLimit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType whereEncashable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LeaveType yesterday(string $column = 'current_at')
 */
	class LeaveType extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * MailSetting Model
 * 
 * Represents email/mail server configuration settings.
 * Supports multiple mail configurations with one marked as default.
 *
 * @property int $id
 * @property string $driver
 * @property string $host
 * @property int $port
 * @property string $from_address
 * @property string $from_name
 * @property string $username
 * @property string $password
 * @property string|null $encryption
 * @property bool $is_default
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting default()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereDriver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereEncryption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereFromAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereFromName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereHost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MailSetting whereUsername($value)
 */
	class MailSetting extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * MobileToken Model
 * 
 * Represents a mobile device token for API authentication.
 *
 * @property int $id
 * @property string $name
 * @property string|null $ip
 * @property string|null $location
 * @property string $token
 * @property bool $is_active
 * @property Carbon|null $last_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|MobileToken active()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereLastActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereLocation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MobileToken whereUpdatedAt($value)
 */
	class MobileToken extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * MoneyTransfer Model
 * 
 * Represents a money transfer between two accounts.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $from_account_id
 * @property int $to_account_id
 * @property float $amount
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Account $fromAccount
 * @property-read Account $toAccount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer whereFromAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer whereToAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MoneyTransfer whereUpdatedAt($value)
 */
	class MoneyTransfer extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Overtime Model
 * 
 * Represents an overtime record for an employee.
 *
 * @property int $id
 * @property int $employee_id
 * @property Carbon $date
 * @property float $hours
 * @property float $rate
 * @property float $amount
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @method static Builder|Overtime paid()
 * @method static Builder|Overtime pending()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Overtime whereUpdatedAt($value)
 */
	class Overtime extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * PackingSlip Model
 * 
 * Represents a packing slip for a sale delivery.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $sale_id
 * @property int|null $delivery_id
 * @property float $amount
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sale $sale
 * @property-read Delivery|null $delivery
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, PackingSlipProduct> $packingSlipProducts
 * @method static Builder|PackingSlip pending()
 * @method static Builder|PackingSlip completed()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $packing_slip_products_count
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereDeliveryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlip whereUpdatedAt($value)
 */
	class PackingSlip extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * PackingSlipProduct Model (Pivot)
 * 
 * Represents the relationship between packing slips and products.
 *
 * @property int $id
 * @property int $packing_slip_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read PackingSlip $packingSlip
 * @property-read Product $product
 * @property-read Variant|null $variant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct wherePackingSlipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PackingSlipProduct whereVariantId($value)
 */
	class PackingSlipProduct extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Payment Model
 * 
 * Represents a payment transaction for a sale or purchase.
 *
 * @property int $id
 * @property int|null $purchase_id
 * @property int $user_id
 * @property int|null $sale_id
 * @property int|null $cash_register_id
 * @property int|null $account_id
 * @property string|null $payment_receiver
 * @property string|null $payment_reference
 * @property float $amount
 * @property int|null $currency_id
 * @property int|null $installment_id
 * @property float|null $exchange_rate
 * @property Carbon $payment_at
 * @property float|null $used_points
 * @property float|null $change
 * @property string $paying_method
 * @property string|null $payment_proof
 * @property string|null $document
 * @property string|null $payment_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Purchase|null $purchase
 * @property-read Sale|null $sale
 * @property-read User $user
 * @property-read CashRegister|null $cashRegister
 * @property-read Account|null $account
 * @property-read Currency|null $currency
 * @property-read Installment|null $installment
 * @property-read PaymentWithCheque|null $cheque
 * @property-read PaymentWithCreditCard|null $creditCard
 * @property-read PaymentWithGiftCard|null $giftCard
 * @property-read PaymentWithPaypal|null $paypal
 * @method static Builder|Payment byMethod(string $method)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCashRegisterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereChange($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereInstallmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePayingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentReceiver($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePaymentReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUsedPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payment whereUserId($value)
 */
	class Payment extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * PaymentWithCheque Model
 * 
 * Represents cheque payment details for a payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property string $cheque_no
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment $payment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque whereChequeNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCheque whereUpdatedAt($value)
 */
	class PaymentWithCheque extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * PaymentWithCreditCard Model
 * 
 * Represents credit card payment details for a payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property int|null $customer_id
 * @property string|null $customer_stripe_id
 * @property string|null $charge_id
 * @property string|null $data
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment $payment
 * @property-read Customer|null $customer
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereChargeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereCustomerStripeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithCreditCard whereUpdatedAt($value)
 */
	class PaymentWithCreditCard extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * PaymentWithGiftCard Model
 * 
 * Represents gift card payment details for a payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property int $gift_card_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment $payment
 * @property-read GiftCard $giftCard
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard whereGiftCardId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithGiftCard whereUpdatedAt($value)
 */
	class PaymentWithGiftCard extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * PaymentWithPaypal Model
 * 
 * Represents PayPal payment details for a payment.
 *
 * @property int $id
 * @property int $payment_id
 * @property string $transaction_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Payment $payment
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal wherePaymentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal whereTransactionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PaymentWithPaypal whereUpdatedAt($value)
 */
	class PaymentWithPaypal extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Payroll Model
 * 
 * Represents a payroll payment for an employee.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $employee_id
 * @property int|null $account_id
 * @property int $user_id
 * @property float $amount
 * @property string $paying_method
 * @property string|null $note
 * @property string $status
 * @property array|null $amount_array
 * @property string|null $month
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Employee $employee
 * @property-read Account|null $account
 * @property-read User $user
 * @method static Builder|Payroll paid()
 * @method static Builder|Payroll pending()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereAmountArray($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereEmployeeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereMonth($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll wherePayingMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Payroll whereUserId($value)
 */
	class Payroll extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Custom Permission model extending Spatie with module and is_active support.
 *
 * @property int $id
 * @property string $name
 * @property string $guard_name
 * @property bool $is_active
 * @property string|null $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Permission active()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Permission withoutRole($roles, $guard = null)
 */
	class Permission extends \Eloquent {}
}

namespace App\Models{
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
 */
	class PosSetting extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Printer Model
 * 
 * Represents a printer configuration for a warehouse.
 *
 * @property int $id
 * @property string $name
 * @property int $warehouse_id
 * @property string $connection_type
 * @property string $capability_profile
 * @property string|null $char_per_line
 * @property string|null $ip_address
 * @property string|null $port
 * @property string|null $path
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Warehouse $warehouse
 * @method static Builder|Printer active()
 * @property int $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read string $capability_profile_str
 * @property-read string $connection_type_str
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereCapabilityProfile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereCharPerLine($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereConnectionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer wherePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer wherePort($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Printer whereWarehouseId($value)
 */
	class Printer extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Product Model
 * 
 * Represents a product in the inventory system with support for variants, batches, and multiple pricing.
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $type
 * @property string|null $slug
 * @property string $barcode_symbology
 * @property int|null $brand_id
 * @property int $category_id
 * @property int $unit_id
 * @property int $purchase_unit_id
 * @property int $sale_unit_id
 * @property float $cost
 * @property float|null $profit_margin
 * @property string|null $profit_margin_type
 * @property float $price
 * @property float|null $wholesale_price
 * @property float|null $qty
 * @property float|null $alert_quantity
 * @property float|null $daily_sale_objective
 * @property bool|null $promotion
 * @property float|null $promotion_price
 * @property \Illuminate\Support\Carbon|null $starting_date
 * @property \Illuminate\Support\Carbon|null $last_date
 * @property int|null $tax_id
 * @property int|null $tax_method
 * @property array|null $image
 * @property array|null $image_url
 * @property string|null $file
 * @property string|null $file_url
 * @property bool|null $is_embeded
 * @property bool $is_batch
 * @property bool $is_variant
 * @property bool $is_diff_price
 * @property bool $is_imei
 * @property bool|null $featured
 * @property string|null $product_list
 * @property string|null $variant_list
 * @property string|null $qty_list
 * @property string|null $price_list
 * @property array|null $product_details
 * @property string|null $short_description
 * @property string|null $specification
 * @property string|null $related_products
 * @property bool|null $is_addon
 * @property string|null $extras
 * @property string|null $menu_type
 * @property string|null $variant_option
 * @property string|null $variant_value
 * @property bool $is_active
 * @property bool|null $is_online
 * @property int|null $kitchen_id
 * @property bool|null $in_stock
 * @property bool $track_inventory
 * @property bool|null $is_sync_disable
 * @property int|null $woocommerce_product_id
 * @property int|null $woocommerce_media_id
 * @property string|null $tags
 * @property string|null $meta_title
 * @property string|null $meta_description
 * @property int|null $warranty
 * @property int|null $guarantee
 * @property string|null $warranty_type
 * @property string|null $guarantee_type
 * @property float|null $wastage_percent
 * @property int|null $combo_unit_id
 * @property float|null $production_cost
 * @property bool|null $is_recipe
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Category $category
 * @property-read Brand|null $brand
 * @property-read Tax|null $tax
 * @property-read Unit $unit
 * @property-read Unit $purchaseUnit
 * @property-read Unit $saleUnit
 * @property-read Collection<int, Variant> $variants
 * @property-read Collection<int, Warehouse> $warehouses
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, ProductBatch> $batches
 * @property-read Collection<int, ProductVariant> $productVariants
 * @property-read Collection<int, ProductWarehouse> $productWarehouses
 * @method static Builder|Product active()
 * @method static Builder|Product activeStandard()
 * @method static Builder|Product activeFeatured()
 * @method static Builder|Product featured()
 * @method static Builder|Product online()
 * @property string|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $batches_count
 * @property-read int|null $product_variants_count
 * @property-read int|null $product_warehouses_count
 * @property-read int|null $purchases_count
 * @property-read int|null $sales_count
 * @property-read int|null $variants_count
 * @property-read int|null $warehouses_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereAlertQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBarcodeSymbology($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereComboUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDailySaleObjective($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereFileUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGuarantee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereGuaranteeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereInStock($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsBatch($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsDiffPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsEmbeded($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsImei($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsOnline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsRecipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsSyncDisable($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereIsVariant($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereLastDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMetaDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereMetaTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePriceList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductDetails($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProductionCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProfitMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereProfitMarginType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePromotion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePromotionPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product wherePurchaseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereQtyList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereRelatedProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSaleUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereShortDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereSpecification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereStartingDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTags($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTaxMethod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereTrackInventory($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVariantList($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVariantOption($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereVariantValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWarranty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWarrantyType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWastagePercent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWholesalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWoocommerceMediaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Product whereWoocommerceProductId($value)
 */
	class Product extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductAdjustment Model (Pivot)
 * 
 * Represents the relationship between adjustments and products with adjustment details.
 *
 * @property int $id
 * @property int $adjustment_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property float $unit_cost
 * @property float $qty
 * @property string $action
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Adjustment $adjustment
 * @property-read Product $product
 * @property-read Variant|null $variant
 * @method static Builder|ProductAdjustment add()
 * @method static Builder|ProductAdjustment subtract()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereAdjustmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductAdjustment whereVariantId($value)
 */
	class ProductAdjustment extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductBatch Model
 * 
 * Represents a product batch with expiry date for batch tracking.
 *
 * @property int $id
 * @property int $product_id
 * @property string $batch_no
 * @property Carbon|null $expired_date
 * @property float $qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read Collection<int, ProductSale> $productSales
 * @property-read Collection<int, ProductPurchase> $productPurchases
 * @method static Builder|ProductBatch expired()
 * @method static Builder|ProductBatch expiringSoon(int $days = 30)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_purchases_count
 * @property-read int|null $product_sales_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch whereBatchNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch whereExpiredDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductBatch whereUpdatedAt($value)
 */
	class ProductBatch extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductPurchase Model (Pivot)
 * 
 * Represents the relationship between products and purchases with additional purchase-specific data.
 *
 * @property int $id
 * @property int $purchase_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property float $recieved
 * @property float $return_qty
 * @property int $purchase_unit_id
 * @property float $net_unit_cost
 * @property float $net_unit_price
 * @property float|null $net_unit_margin
 * @property string|null $net_unit_margin_type
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Purchase $purchase
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @method static Builder|ProductPurchase received()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereNetUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereNetUnitMargin($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereNetUnitMarginType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereNetUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase wherePurchaseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereRecieved($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereReturnQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductPurchase whereVariantId($value)
 */
	class ProductPurchase extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductQuotation Model (Pivot)
 * 
 * Represents the relationship between quotations and products.
 *
 * @property int $id
 * @property int $quotation_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property float $qty
 * @property int $sale_unit_id
 * @property float $net_unit_price
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Quotation $quotation
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @property-read Unit $unit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereNetUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereQuotationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereSaleUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductQuotation whereVariantId($value)
 */
	class ProductQuotation extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductReturn Model (Pivot)
 * 
 * Represents the relationship between returns and products with return-specific data.
 *
 * @property int $id
 * @property int $return_id
 * @property int $product_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property int|null $product_batch_id
 * @property float $qty
 * @property int $sale_unit_id
 * @property float $net_unit_price
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Returns $return
 * @property-read Product $product
 * @property-read Variant|null $variant
 * @property-read ProductBatch|null $batch
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereNetUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereReturnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereSaleUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductReturn whereVariantId($value)
 */
	class ProductReturn extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductSale Model (Pivot)
 * 
 * Represents the relationship between products and sales with additional sale-specific data.
 *
 * @property int $id
 * @property int $sale_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property float $return_qty
 * @property int $sale_unit_id
 * @property float $net_unit_price
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property bool|null $is_packing
 * @property bool $is_delivered
 * @property int|null $topping_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Sale $sale
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @method static Builder|ProductSale delivered()
 * @method static Builder|ProductSale notDelivered()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereIsDelivered($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereIsPacking($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereNetUnitPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereReturnQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereSaleUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSale whereVariantId($value)
 */
	class ProductSale extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductSupplier Model (Pivot)
 * 
 * Represents the relationship between products and suppliers with supplier-specific pricing.
 *
 * @property int $id
 * @property string $product_code
 * @property int $supplier_id
 * @property float $qty
 * @property float $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Supplier $supplier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereProductCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductSupplier whereUpdatedAt($value)
 */
	class ProductSupplier extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductTransfer Model (Pivot)
 * 
 * Represents the relationship between transfers and products.
 *
 * @property int $id
 * @property int $transfer_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property int $purchase_unit_id
 * @property float $net_unit_cost
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Transfer $transfer
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @property-read Unit $unit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereNetUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer wherePurchaseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereTransferId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductTransfer whereVariantId($value)
 */
	class ProductTransfer extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductVariant Model (Pivot)
 * 
 * Represents the relationship between products and variants with variant-specific pricing and stock.
 *
 * @property int $id
 * @property int $product_id
 * @property int $variant_id
 * @property int $position
 * @property string|null $item_code
 * @property float $additional_cost
 * @property float $additional_price
 * @property float $qty
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read Variant $variant
 * @method static Builder|ProductVariant findExactProduct(int $productId, int $variantId)
 * @method static Builder|ProductVariant findExactProductWithCode(int $productId, string $itemCode)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereAdditionalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereAdditionalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereItemCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant wherePosition($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductVariant whereVariantId($value)
 */
	class ProductVariant extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ProductWarehouse Model (Pivot)
 * 
 * Represents the relationship between products and warehouses with stock quantity information.
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property int $warehouse_id
 * @property float $qty
 * @property float|null $price
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Product $product
 * @property-read Warehouse $warehouse
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @method static Builder|ProductWarehouse findProductWithVariant(int $productId, int $variantId, int $warehouseId)
 * @method static Builder|ProductWarehouse findProductWithoutVariant(int $productId, int $warehouseId)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereVariantId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ProductWarehouse whereWarehouseId($value)
 */
	class ProductWarehouse extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Purchase Model
 * 
 * Represents a purchase transaction in the system.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int $warehouse_id
 * @property int $supplier_id
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_cost
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float|null $order_discount
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property float $paid_amount
 * @property string $status
 * @property string $payment_status
 * @property string|null $document
 * @property string|null $note
 * @property string|null $purchase_type
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read Supplier $supplier
 * @property-read Warehouse $warehouse
 * @property-read Currency|null $currency
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, ProductPurchase> $productPurchases
 * @property-read Collection<int, ReturnPurchase> $returns
 * @property-read Collection<int, Payment> $payments
 * @property-read InstallmentPlan|null $installmentPlan
 * @property-read User|null $deleter
 * @method static Builder|Purchase completed()
 * @method static Builder|Purchase pending()
 * @method static Builder|Purchase paid()
 * @method static Builder|Purchase unpaid()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $payments_count
 * @property-read int|null $product_purchases_count
 * @property-read int|null $products_count
 * @property-read int|null $returns_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereOrderDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereOrderTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereOrderTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase wherePurchaseType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Purchase withoutTrashed()
 */
	class Purchase extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * PurchaseProductReturn Model (Pivot)
 * 
 * Represents the relationship between purchase returns and products with return-specific data.
 *
 * @property int $id
 * @property int $return_id
 * @property int $product_id
 * @property int|null $product_batch_id
 * @property int|null $variant_id
 * @property string|null $imei_number
 * @property float $qty
 * @property int $purchase_unit_id
 * @property float $net_unit_cost
 * @property float $discount
 * @property float $tax_rate
 * @property float $tax
 * @property float $total
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read ReturnPurchase $purchaseReturn
 * @property-read Product $product
 * @property-read ProductBatch|null $batch
 * @property-read Variant|null $variant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereImeiNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereNetUnitCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereProductBatchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereProductId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn wherePurchaseUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereReturnId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseProductReturn whereVariantId($value)
 */
	class PurchaseProductReturn extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Quotation Model
 * 
 * Represents a quotation/quote for a customer or supplier.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int $biller_id
 * @property int|null $supplier_id
 * @property int|null $customer_id
 * @property int $warehouse_id
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_price
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float|null $order_discount
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property string $quotation_status
 * @property string|null $document
 * @property string|null $note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Biller $biller
 * @property-read Supplier|null $supplier
 * @property-read Customer|null $customer
 * @property-read Warehouse $warehouse
 * @property-read Collection<int, ProductQuotation> $productQuotations
 * @method static Builder|Quotation pending()
 * @method static Builder|Quotation accepted()
 * @method static Builder|Quotation rejected()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_quotations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereBillerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereOrderDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereOrderTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereOrderTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereQuotationStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Quotation whereWarehouseId($value)
 */
	class Quotation extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * ReturnPurchase Model
 * 
 * Represents a return transaction for a purchase.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $purchase_id
 * @property int $user_id
 * @property int $supplier_id
 * @property int $warehouse_id
 * @property int|null $account_id
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_cost
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float $grand_total
 * @property string|null $document
 * @property string|null $return_note
 * @property string|null $staff_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Purchase $purchase
 * @property-read User $user
 * @property-read Supplier $supplier
 * @property-read Warehouse $warehouse
 * @property-read Account|null $account
 * @property-read Currency|null $currency
 * @property-read Collection<int, PurchaseProductReturn> $purchaseProductReturns
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $purchase_product_returns_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereOrderTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereOrderTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase wherePurchaseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereReturnNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereStaffNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturnPurchase whereWarehouseId($value)
 */
	class ReturnPurchase extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Returns Model (Sale Return)
 * 
 * Represents a return transaction for a sale.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int $sale_id
 * @property int|null $cash_register_id
 * @property int|null $customer_id
 * @property int $warehouse_id
 * @property int $biller_id
 * @property int|null $account_id
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_price
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property float $grand_total
 * @property string|null $document
 * @property string|null $return_note
 * @property string|null $staff_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Sale $sale
 * @property-read Customer|null $customer
 * @property-read Warehouse $warehouse
 * @property-read Biller $biller
 * @property-read Account|null $account
 * @property-read Currency|null $currency
 * @property-read Collection<int, ProductReturn> $productReturns
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_returns_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereBillerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereCashRegisterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereOrderTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereOrderTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereReturnNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereStaffNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Returns whereWarehouseId($value)
 */
	class Returns extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * RewardPoint Model
 * 
 * Represents a reward point transaction for a customer.
 *
 * @property int $id
 * @property int $customer_id
 * @property string $reward_point_type
 * @property float $points
 * @property float $deducted_points
 * @property string|null $note
 * @property Carbon|null $expired_at
 * @property int|null $created_by
 * @property int|null $updated_by
 * @property int|null $sale_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Customer $customer
 * @property-read User|null $creator
 * @property-read Sale|null $sale
 * @method static Builder|RewardPoint expired()
 * @method static Builder|RewardPoint notExpired()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereDeductedPoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereExpiredAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint wherePoints($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereRewardPointType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereSaleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPoint whereUpdatedBy($value)
 */
	class RewardPoint extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * RewardPointSetting Model
 * 
 * Represents reward points system configuration settings.
 *
 * @property int $id
 * @property float $per_point_amount
 * @property float $minimum_amount
 * @property int $duration
 * @property string $type
 * @property bool $is_active
 * @property float $redeem_amount_per_unit_rp
 * @property float $min_order_total_for_redeem
 * @property int $min_redeem_point
 * @property int $max_redeem_point
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMaxRedeemPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMinOrderTotalForRedeem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMinRedeemPoint($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereMinimumAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting wherePerPointAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereRedeemAmountPerUnitRp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RewardPointSetting whereUpdatedAt($value)
 */
	class RewardPointSetting extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Role Model
 * 
 * Extends Spatie Permission Role with description, module, and is_active.
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $guard_name
 * @property bool $is_active
 * @property string|null $module
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|Role active()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereGuardName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereModule($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Role withoutPermission($permissions)
 */
	class Role extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Sale Model
 * 
 * Represents a sale transaction in the system.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property int|null $cash_register_id
 * @property int|null $table_id
 * @property int|null $queue
 * @property int|null $customer_id
 * @property int $warehouse_id
 * @property int $biller_id
 * @property int $item
 * @property float $total_qty
 * @property float $total_discount
 * @property float $total_tax
 * @property float $total_price
 * @property float|null $order_tax_rate
 * @property float|null $order_tax
 * @property string|null $order_discount_type
 * @property float|null $order_discount_value
 * @property float|null $order_discount
 * @property int|null $coupon_id
 * @property float|null $coupon_discount
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property int|null $currency_id
 * @property float|null $exchange_rate
 * @property string $sale_status
 * @property string $payment_status
 * @property string|null $billing_name
 * @property string|null $billing_phone
 * @property string|null $billing_email
 * @property string|null $billing_address
 * @property string|null $billing_city
 * @property string|null $billing_state
 * @property string|null $billing_country
 * @property string|null $billing_zip
 * @property string|null $shipping_name
 * @property string|null $shipping_phone
 * @property string|null $shipping_email
 * @property string|null $shipping_address
 * @property string|null $shipping_city
 * @property string|null $shipping_state
 * @property string|null $shipping_country
 * @property string|null $shipping_zip
 * @property string $sale_type
 * @property int|null $service_id
 * @property int|null $waiter_id
 * @property float $paid_amount
 * @property string|null $document
 * @property string|null $sale_note
 * @property string|null $staff_note
 * @property int|null $woocommerce_order_id
 * @property int|null $deleted_by
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read User $user
 * @property-read Customer|null $customer
 * @property-read Warehouse $warehouse
 * @property-read Biller $biller
 * @property-read Table|null $table
 * @property-read CashRegister|null $cashRegister
 * @property-read Currency|null $currency
 * @property-read Coupon|null $coupon
 * @property-read Collection<int, Product> $products
 * @property-read Collection<int, ProductSale> $productSales
 * @property-read Collection<int, Payment> $payments
 * @property-read Delivery|null $delivery
 * @property-read Returns|null $return
 * @property-read InstallmentPlan|null $installmentPlan
 * @property-read User|null $deleter
 * @method static Builder|Sale completed()
 * @method static Builder|Sale pending()
 * @method static Builder|Sale paid()
 * @method static Builder|Sale unpaid()
 * @property int $steadfast
 * @property string|null $payment_mode
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $payments_count
 * @property-read int|null $product_sales_count
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereBillingZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCashRegisterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCouponDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCouponId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCurrencyId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereCustomerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereDeletedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereExchangeRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereOrderDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereOrderDiscountType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereOrderDiscountValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereOrderTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereOrderTaxRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale wherePaidAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale wherePaymentMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale wherePaymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereQueue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereSaleNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereSaleStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereSaleType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingState($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereShippingZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereStaffNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereSteadfast($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTotalDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale whereWoocommerceOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Sale withoutTrashed()
 */
	class Sale extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Shift Model
 * 
 * Represents a work shift with time schedules.
 *
 * @property int $id
 * @property string $name
 * @property string $start_time
 * @property string $end_time
 * @property int|null $grace_in
 * @property int|null $grace_out
 * @property float|null $total_hours
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Employee> $employees
 * @method static Builder|Shift active()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $employees_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereEndTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereGraceIn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereGraceOut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereStartTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereTotalHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Shift whereUpdatedAt($value)
 */
	class Shift extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * SmsTemplate Model
 * 
 * Represents an SMS message template.
 *
 * @property int $id
 * @property string $name
 * @property string $content
 * @property bool $is_default
 * @property bool $is_default_ecommerce
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|SmsTemplate default()
 * @method static Builder|SmsTemplate defaultEcommerce()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate whereContent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate whereIsDefault($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate whereIsDefaultEcommerce($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SmsTemplate whereUpdatedAt($value)
 */
	class SmsTemplate extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class State
 * 
 * Represents a state/region from World reference data. Extends Nnjeim\World State.
 *
 * @property int $id
 * @property string $name
 * @property string|null $code
 * @property int|null $country_id
 * @property string|null $country_code
 * @property string|null $state_code
 * @property string|null $type
 * @property string|null $latitude
 * @property string|null $longitude
 * @method static Builder|State newModelQuery()
 * @method static Builder|State newQuery()
 * @method static Builder|State query()
 * @method static Builder|State filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\City> $cities
 * @property-read int|null $cities_count
 * @property-read \App\Models\Country|null $country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereLatitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereLongitude($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereStateCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|State yesterday(string $column = 'current_at')
 */
	class State extends \Eloquent {}
}

namespace App\Models{
/**
 * StockCount Model
 * 
 * Represents a stock count/inventory audit for a warehouse.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $warehouse_id
 * @property int|null $brand_id
 * @property int|null $category_id
 * @property int $user_id
 * @property string $type
 * @property string|null $initial_file
 * @property string|null $final_file
 * @property string|null $note
 * @property bool $is_adjusted
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Warehouse $warehouse
 * @property-read Brand|null $brand
 * @property-read Category|null $category
 * @property-read User $user
 * @method static Builder|StockCount adjusted()
 * @method static Builder|StockCount notAdjusted()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereFinalFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereInitialFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereIsAdjusted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StockCount whereWarehouseId($value)
 */
	class StockCount extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Supplier Model
 * 
 * Represents a supplier/vendor in the system.
 * Follows the same structure as Customer: country_id, state_id, city_id, scopeFilter, active scope.
 *
 * @property int $id
 * @property string $name
 * @property string|null $image
 * @property string|null $image_url
 * @property string|null $company_name
 * @property string|null $vat_number
 * @property string|null $email
 * @property string|null $phone_number
 * @property string|null $wa_number
 * @property string|null $address
 * @property int|null $country_id
 * @property int|null $state_id
 * @property int|null $city_id
 * @property string|null $postal_code
 * @property float $opening_balance
 * @property int|null $pay_term_no
 * @property string|null $pay_term_period
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Country|null $country
 * @property-read State|null $state
 * @property-read City|null $city
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, ReturnPurchase> $returnPurchases
 * @property-read Collection<int, Product> $products
 * @method static Builder|Supplier active()
 * @method static Builder|Supplier filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $products_count
 * @property-read int|null $purchases_count
 * @property-read int|null $return_purchases_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCityId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereImage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereImageUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereOpeningBalance($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePayTermNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePayTermPeriod($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereStateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereVatNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereWaNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier withoutTrashed()
 */
	class Supplier extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Table Model
 * 
 * Represents a restaurant table for POS system.
 *
 * @property int $id
 * @property string $name
 * @property int $number_of_person
 * @property string|null $description
 * @property int|null $floor_id
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Sale> $sales
 * @method static Builder|Table active()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $sales_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereFloorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereNumberOfPerson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Table whereUpdatedAt($value)
 */
	class Table extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Tax
 * 
 * Represents a tax rate within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for tax entities.
 *
 * @property int $id
 * @property string $name
 * @property float $rate
 * @property bool $is_active
 * @property int|null $woocommerce_tax_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Tax newModelQuery()
 * @method static Builder|Tax newQuery()
 * @method static Builder|Tax query()
 * @method static Builder|Tax active()
 * @method static Builder|Tax filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax whereWoocommerceTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Tax yesterday(string $column = 'current_at')
 */
	class Tax extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Timezone
 * 
 * Represents a timezone from World reference data. Extends Nnjeim\World Timezone.
 *
 * @property int $id
 * @property int $country_id
 * @property string $name
 * @method static Builder|Timezone newModelQuery()
 * @method static Builder|Timezone newQuery()
 * @method static Builder|Timezone query()
 * @method static Builder|Timezone filter(array $filters)
 * @property-read \App\Models\Country|null $country
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereCountryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Timezone yesterday(string $column = 'current_at')
 */
	class Timezone extends \Eloquent {}
}

namespace App\Models{
/**
 * Transfer Model
 * 
 * Represents a stock transfer between warehouses.
 *
 * @property int $id
 * @property string $reference_no
 * @property int $user_id
 * @property string $status
 * @property int $from_warehouse_id
 * @property int $to_warehouse_id
 * @property int $item
 * @property float $total_qty
 * @property float $total_tax
 * @property float $total_cost
 * @property float|null $shipping_cost
 * @property float $grand_total
 * @property string|null $document
 * @property string|null $note
 * @property bool $is_sent
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read User $user
 * @property-read Warehouse $fromWarehouse
 * @property-read Warehouse $toWarehouse
 * @property-read Collection<int, ProductTransfer> $productTransfers
 * @method static Builder|Transfer pending()
 * @method static Builder|Transfer completed()
 * @method static Builder|Transfer sent()
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $product_transfers_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereDocument($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereFromWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereIsSent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereItem($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereReferenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereShippingCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereToWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereTotalCost($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereTotalQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Transfer whereUserId($value)
 */
	class Transfer extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Translation Model
 * 
 * Represents a translation entry for a language.
 *
 * @property int $id
 * @property string $locale
 * @property string $group
 * @property string $key
 * @property string $value
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Language $language
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation whereGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Translation whereValue($value)
 */
	class Translation extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Unit
 * 
 * Represents a measurement unit within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for unit entities.
 * Supports base-unit conversion (e.g. kg as base, g as derived).
 *
 * @property int $id
 * @property string $code
 * @property string $name
 * @property int|null $base_unit
 * @property string|null $operator
 * @property float|null $operation_value
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Unit newModelQuery()
 * @method static Builder|Unit newQuery()
 * @method static Builder|Unit query()
 * @method static Builder|Unit active()
 * @method static Builder|Unit filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read Unit|null $baseUnitRelation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Unit> $subUnits
 * @property-read int|null $sub_units_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereBaseUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereOperationValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Unit yesterday(string $column = 'current_at')
 */
	class Unit extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * User Model
 * 
 * Represents a user in the system with authentication, roles, and permissions.
 *
 * @property int $id
 * @property string $name
 * @property string|null $username
 * @property string $email
 * @property string|null $avatar
 * @property string|null $avatar_url
 * @property string|null $phone
 * @property string|null $company_name
 * @property int|null $biller_id
 * @property int|null $warehouse_id
 * @property int|null $kitchen_id
 * @property bool|null $service_staff
 * @property bool $is_active
 * @property bool $is_deleted
 * @property Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Biller|null $biller
 * @property-read Warehouse|null $warehouse
 * @property-read Collection<int, Holiday> $holidays
 * @property-read Collection<int, Sale> $sales
 * @property-read Collection<int, Purchase> $purchases
 * @property-read Collection<int, Payment> $payments
 * @method static Builder|User active()
 * @method static Builder|User notDeleted()
 * @method static Builder|User filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $holidays_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read int|null $payments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read int|null $purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read int|null $sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User permission($permissions, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User role($roles, $guard = null, $without = false)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereAvatarUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBillerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCompanyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereIsDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKitchenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereServiceStaff($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereWarehouseId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutPermission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User withoutRole($roles, $guard = null)
 */
	class User extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable, \Illuminate\Contracts\Auth\MustVerifyEmail {}
}

namespace App\Models{
/**
 * Variant Model
 * 
 * Represents a product variant option (e.g., Size, Color).
 *
 * @property int $id
 * @property string $name
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Product> $products
 * @method static Builder|Variant byName(string $name)
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read int|null $products_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Variant withoutTrashed()
 */
	class Variant extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * Class Warehouse
 * 
 * Represents a warehouse within the system. Handles the underlying data
 * structure, relationships, and specific query scopes for warehouse entities.
 *
 * @property int $id
 * @property string $name
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @method static Builder|Warehouse newModelQuery()
 * @method static Builder|Warehouse newQuery()
 * @method static Builder|Warehouse query()
 * @method static Builder|Warehouse active()
 * @method static Builder|Warehouse filter(array $filters)
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Printer> $printers
 * @property-read int|null $printers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ProductWarehouse> $productWarehouses
 * @property-read int|null $product_warehouses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Product> $products
 * @property-read int|null $products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Purchase> $purchases
 * @property-read int|null $purchases_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Sale> $sales
 * @property-read int|null $sales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse customRange($startDate = null, $endDate = null, string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse last30Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse last7Days(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse lastQuarter(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse lastYear(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse monthToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse quarterToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse today(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse withoutTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse yearToDate(string $column = 'created_at')
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Warehouse yesterday(string $column = 'current_at')
 */
	class Warehouse extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}

namespace App\Models{
/**
 * WhatsappSetting Model
 * 
 * Represents WhatsApp Business API configuration settings.
 *
 * @property int $id
 * @property string $phone_number_id
 * @property string $business_account_id
 * @property string $permanent_access_token
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \OwenIt\Auditing\Models\Audit> $audits
 * @property-read int|null $audits_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting whereBusinessAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting wherePermanentAccessToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting wherePhoneNumberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|WhatsappSetting whereUpdatedAt($value)
 */
	class WhatsappSetting extends \Eloquent implements \OwenIt\Auditing\Contracts\Auditable {}
}


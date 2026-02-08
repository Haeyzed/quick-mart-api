<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CustomerTypeEnum;
use App\Mail\SaleDetails;
use App\Models\Account;
use App\Models\CashRegister;
use App\Models\Coupon;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\GiftCard;
use App\Models\InstallmentPlan;
use App\Models\MailSetting;
use App\Models\Payment;
use App\Models\PaymentWithCheque;
use App\Models\PaymentWithCreditCard;
use App\Models\PaymentWithGiftCard;
use App\Models\PaymentWithPaypal;
use App\Models\Product;
use App\Models\ProductBatch;
use App\Models\ProductSale;
use App\Models\ProductVariant;
use App\Models\ProductWarehouse;
use App\Models\RewardPoint;
use App\Models\RewardPointSetting;
use App\Models\Sale;
use App\Models\Unit;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

/**
 * SaleService
 *
 * Handles all business logic related to sales, including:
 * - Sale creation and updates
 * - Stock management (variants, batches, IMEI, combo products)
 * - Payment processing (multiple payment methods)
 * - Reward points and coupons
 * - Email notifications
 * - Activity logging
 *
 * @package App\Services
 */
class SaleService extends BaseService
{
    /**
     * Create a new sale with all related data.
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $document
     * @return Sale
     * @throws Exception
     */
    public function createSale(array $data, ?UploadedFile $document = null): Sale
    {
        return $this->transaction(function () use ($data, $document) {
            // Validate reference number if provided
            if (isset($data['reference_no'])) {
                $this->validateReferenceNumber($data['reference_no']);
            }

            // Prepare sale data
            $saleData = $this->prepareSaleData($data, $document);

            // Create sale
            $sale = Sale::create($saleData);

            // Handle custom fields
            $this->handleCustomFields($sale, $data);

            // Process products and update stock
            $this->processSaleProducts($sale, $data);

            // Process payments
            $this->processPayments($sale, $data);

            // Handle reward points
            $this->handleRewardPoints($sale, $data);

            // Handle coupon
            if (isset($data['coupon_active']) && $data['coupon_active'] && !($data['draft'] ?? false)) {
                $this->applyCoupon($data['coupon_id'] ?? null);
            }

            // Send email notification
            if (isset($data['sale_status']) && $data['sale_status'] == 1) {
                $this->sendSaleEmail($sale);
            }

            // Create activity log
            $this->createSaleActivityLog($sale, 'Sale Created');

            // Handle installment plan if enabled
            if (isset($data['enable_installment']) && $data['enable_installment']) {
                $this->createInstallmentPlan($sale, $data['installment_plan'] ?? []);
            }

            return $sale->fresh(['customer', 'warehouse', 'user', 'productSales.product']);
        });
    }

    /**
     * Validate reference number uniqueness.
     *
     * @param string $referenceNo
     * @param int|null $excludeId
     * @return void
     * @throws Exception
     */
    protected function validateReferenceNumber(string $referenceNo, ?int $excludeId = null): void
    {
        $query = Sale::where('reference_no', $referenceNo);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        if ($query->exists()) {
            throw new Exception('Reference number already exists.');
        }
    }

    /**
     * Prepare sale data for create/update.
     *
     * @param array<string, mixed> $data
     * @param UploadedFile|null $document
     * @param Sale|null $existingSale
     * @return array<string, mixed>
     */
    protected function prepareSaleData(
        array         $data,
        ?UploadedFile $document = null,
        ?Sale         $existingSale = null
    ): array
    {
        $saleData = $data;

        // Set user ID
        $saleData['user_id'] = Auth::id();

        // Handle cash register
        $cashRegister = CashRegister::where([
            ['user_id', $saleData['user_id']],
            ['warehouse_id', $saleData['warehouse_id']],
            ['status', true],
        ])->first();

        if ($cashRegister) {
            $saleData['cash_register_id'] = $cashRegister->id;
        }

        // Generate reference number if not provided
        if (!isset($saleData['reference_no'])) {
            $prefix = isset($data['pos']) ? 'posr-' : 'sr-';
            $saleData['reference_no'] = $this->generateReferenceNumber($prefix);
        }

        // Handle document upload
        if ($document) {
            $saleData['document'] = $this->handleDocumentUpload($document);
        } elseif ($existingSale) {
            $saleData['document'] = $existingSale->document;
        }

        // Handle paid amount (can be array for multiple payments)
        if (is_array($saleData['paid_amount'] ?? null)) {
            $saleData['paid_amount'] = array_sum($saleData['paid_amount']);
        }

        // Calculate payment status
        $balance = ($saleData['grand_total'] ?? 0) - ($saleData['paid_amount'] ?? 0);
        if ($balance > 0 || $balance < 0) {
            $saleData['payment_status'] = 2; // Partial
        } else {
            $saleData['payment_status'] = 4; // Paid
        }

        // Handle created_at
        if (isset($saleData['created_at'])) {
            $saleData['created_at'] = $this->normalizeDateTime($saleData['created_at']);
        } else {
            $saleData['created_at'] = now();
        }

        // Handle queue number for restaurant
        if (isset($saleData['table_id'])) {
            $latestSale = Sale::whereNotNull('table_id')
                ->whereNull('deleted_at')
                ->whereDate('created_at', now()->toDateString())
                ->where('warehouse_id', $saleData['warehouse_id'])
                ->select('queue')
                ->orderBy('id', 'desc')
                ->first();

            $saleData['queue'] = $latestSale ? ($latestSale->queue + 1) : 1;
        }

        return $saleData;
    }

    /**
     * Generate unique reference number.
     *
     * @param string $prefix
     * @return string
     */
    protected function generateReferenceNumber(string $prefix): string
    {
        $date = date('Ymd');
        $time = date('his');
        $referenceNo = $prefix . $date . '-' . $time;

        // Ensure uniqueness
        while (Sale::where('reference_no', $referenceNo)->exists()) {
            $time = date('his');
            $referenceNo = $prefix . $date . '-' . $time;
        }

        return $referenceNo;
    }

    /**
     * Handle document upload.
     *
     * @param UploadedFile $document
     * @return string
     */
    protected function handleDocumentUpload(UploadedFile $document): string
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'csv', 'docx', 'xlsx', 'txt'];
        $ext = strtolower($document->getClientOriginalExtension());

        if (!in_array($ext, $allowedExtensions)) {
            throw new Exception('Invalid document file type.');
        }

        $documentName = date('Ymdhis');
        if (!config('database.connections.saleprosaas_landlord')) {
            $documentName = $documentName . '.' . $ext;
        } else {
            $tenantId = $this->getTenantId();
            $documentName = $tenantId . '_' . $documentName . '.' . $ext;
        }

        $document->move(public_path('documents/sale'), $documentName);

        return $documentName;
    }

    /**
     * Get tenant ID (for multi-tenant support).
     *
     * @return string|null
     */
    protected function getTenantId(): ?string
    {
        // Implement based on your tenant system
        return null;
    }

    /**
     * Normalize datetime string to SQL format.
     *
     * @param string $dateTime
     * @return string
     */
    protected function normalizeDateTime(string $dateTime): string
    {
        try {
            return date('Y-m-d H:i:s', strtotime($dateTime));
        } catch (Exception $e) {
            return now()->toDateTimeString();
        }
    }

    /**
     * Handle custom fields for sale.
     *
     * @param Sale $sale
     * @param array<string, mixed> $data
     * @return void
     */
    protected function handleCustomFields(Sale $sale, array $data): void
    {
        $customFields = CustomField::where('belongs_to', 'sale')
            ->select('name', 'type')
            ->get();

        $customFieldData = [];

        foreach ($customFields as $customField) {
            $fieldName = str_replace(' ', '_', strtolower($customField->name));

            if (isset($data[$fieldName])) {
                if (in_array($customField->type, ['checkbox', 'multi_select'])) {
                    $customFieldData[$fieldName] = implode(',', (array)$data[$fieldName]);
                } else {
                    $customFieldData[$fieldName] = $data[$fieldName];
                }
            }
        }

        if (count($customFieldData) > 0) {
            DB::table('sales')
                ->where('id', $sale->id)
                ->update($customFieldData);
        }
    }

    /**
     * Process sale products and update stock.
     *
     * @param Sale $sale
     * @param array<string, mixed> $data
     * @return void
     * @throws Exception
     */
    protected function processSaleProducts(Sale $sale, array $data): void
    {
        $productIds = $data['product_id'] ?? [];
        $qtys = $data['qty'] ?? [];
        $saleUnits = $data['sale_unit'] ?? [];
        $netUnitPrices = $data['net_unit_price'] ?? [];
        $discounts = $data['discount'] ?? [];
        $taxRates = $data['tax_rate'] ?? [];
        $taxes = $data['tax'] ?? [];
        $totals = $data['subtotal'] ?? [];
        $productCodes = $data['product_code'] ?? [];
        $productBatchIds = $data['product_batch_id'] ?? [];
        $imeiNumbers = $data['imei_number'] ?? [];
        $saleStatus = $data['sale_status'] ?? $sale->sale_status;

        foreach ($productIds as $i => $productId) {
            $product = Product::findOrFail($productId);

            // Handle combo products
            if ($product->type === 'combo' && $saleStatus == 1) {
                $this->processComboProduct($product, (float)$qtys[$i], $saleUnits[$i] ?? 'n/a', $data['warehouse_id']);
            }

            // Process regular product
            $this->processProductSale(
                $sale,
                $product,
                (float)$qtys[$i],
                $saleUnits[$i] ?? 'n/a',
                (float)($netUnitPrices[$i] ?? 0),
                (float)($discounts[$i] ?? 0),
                (float)($taxRates[$i] ?? 0),
                (float)($taxes[$i] ?? 0),
                (float)($totals[$i] ?? 0),
                $productCodes[$i] ?? null,
                $productBatchIds[$i] ?? null,
                $imeiNumbers[$i] ?? null,
                $data['warehouse_id'],
                $saleStatus,
                $data['topping_product'][$i] ?? null
            );
        }
    }

    /**
     * Process combo product and update child product stock.
     *
     * @param Product $comboProduct
     * @param float $qty
     * @param string $saleUnit
     * @param int $warehouseId
     * @return void
     * @throws Exception
     */
    protected function processComboProduct(Product $comboProduct, float $qty, string $saleUnit, int $warehouseId): void
    {
        $productList = explode(',', $comboProduct->product_list);
        $variantList = $comboProduct->variant_list ? explode(',', $comboProduct->variant_list) : [];
        $qtyList = explode(',', $comboProduct->qty_list);

        // Handle sale unit conversion
        $convertedQty = $qty;
        if ($saleUnit !== 'n/a') {
            $saleUnitData = Unit::where('unit_name', $saleUnit)->first();
            if ($saleUnitData) {
                if ($saleUnitData->operator === '*') {
                    $convertedQty = $qty * $saleUnitData->operation_value;
                } elseif ($saleUnitData->operator === '/') {
                    $convertedQty = $qty / $saleUnitData->operation_value;
                }
            }
        }

        foreach ($productList as $key => $childId) {
            $childProduct = Product::find($childId);
            if (!$childProduct) {
                continue;
            }

            $childQty = $convertedQty * (float)($qtyList[$key] ?? 1);

            // Update child product stock
            $childProduct->qty -= $childQty;
            $childProduct->save();

            // Handle variant
            if (isset($variantList[$key]) && $variantList[$key]) {
                $childVariant = ProductVariant::where([
                    ['product_id', $childId],
                    ['variant_id', $variantList[$key]],
                ])->first();

                if ($childVariant) {
                    $childVariant->qty -= $childQty;
                    $childVariant->save();
                }

                $childWarehouse = ProductWarehouse::where([
                    ['product_id', $childId],
                    ['variant_id', $variantList[$key]],
                    ['warehouse_id', $warehouseId],
                ])->first();
            } else {
                $childWarehouse = ProductWarehouse::where([
                    ['product_id', $childId],
                    ['warehouse_id', $warehouseId],
                ])->whereNull('variant_id')
                    ->first();
            }

            if ($childWarehouse) {
                $childWarehouse->qty -= $childQty;
                $childWarehouse->save();
            }
        }
    }

    /**
     * Process a single product sale.
     *
     * @param Sale $sale
     * @param Product $product
     * @param float $qty
     * @param string $saleUnit
     * @param float $netUnitPrice
     * @param float $discount
     * @param float $taxRate
     * @param float $tax
     * @param float $total
     * @param string|null $productCode
     * @param int|null $productBatchId
     * @param string|null $imeiNumber
     * @param int $warehouseId
     * @param int $saleStatus
     * @param mixed $toppingId
     * @return void
     * @throws Exception
     */
    protected function processProductSale(
        Sale    $sale,
        Product $product,
        float   $qty,
        string  $saleUnit,
        float   $netUnitPrice,
        float   $discount,
        float   $taxRate,
        float   $tax,
        float   $total,
        ?string $productCode,
        ?int    $productBatchId,
        ?string $imeiNumber,
        int     $warehouseId,
        int     $saleStatus,
        mixed   $toppingId = null
    ): void
    {
        $variantId = null;
        $saleUnitId = 0;
        $quantity = $qty;

        // Handle sale unit conversion
        if ($saleUnit !== 'n/a' && $product->type !== 'combo') {
            $saleUnitData = Unit::where('unit_name', $saleUnit)->first();
            if ($saleUnitData) {
                $saleUnitId = $saleUnitData->id;

                if ($saleStatus == 1) {
                    if ($saleUnitData->operator === '*') {
                        $quantity = $qty * $saleUnitData->operation_value;
                    } elseif ($saleUnitData->operator === '/') {
                        $quantity = $qty / $saleUnitData->operation_value;
                    }
                }
            }
        }

        // Handle variant
        if ($product->is_variant && $productCode) {
            $productVariant = ProductVariant::where('product_id', $product->id)
                ->where('item_code', $productCode)
                ->first();

            if ($productVariant) {
                $variantId = $productVariant->variant_id;

                // Update stock if sale is completed
                if ($saleStatus == 1) {
                    $productVariant->qty -= $quantity;
                    $productVariant->save();
                }
            }
        }

        // Update product stock
        if ($saleStatus == 1 && $product->type !== 'combo') {
            $product->qty -= $quantity;
            $product->save();
        }

        // Handle product warehouse
        $productWarehouse = null;
        if ($saleStatus == 1) {
            if ($variantId) {
                $productWarehouse = ProductWarehouse::where([
                    ['product_id', $product->id],
                    ['variant_id', $variantId],
                    ['warehouse_id', $warehouseId],
                ])->first();
            } elseif ($productBatchId) {
                $productWarehouse = ProductWarehouse::where([
                    ['product_batch_id', $productBatchId],
                    ['warehouse_id', $warehouseId],
                ])->first();

                // Update batch quantity
                $productBatch = ProductBatch::find($productBatchId);
                if ($productBatch) {
                    $productBatch->qty -= $quantity;
                    $productBatch->save();
                }
            } else {
                $productWarehouse = ProductWarehouse::where([
                    ['product_id', $product->id],
                    ['warehouse_id', $warehouseId],
                ])->whereNull('variant_id')
                    ->whereNull('product_batch_id')
                    ->first();
            }

            if ($productWarehouse) {
                $productWarehouse->qty -= $quantity;

                // Handle IMEI numbers
                if ($imeiNumber && !str_contains($imeiNumber, 'null')) {
                    $imeiNumbers = explode(',', $imeiNumber);
                    $allImeiNumbers = explode(',', $productWarehouse->imei_number ?? '');
                    $allImeiNumbers = array_filter($allImeiNumbers);

                    foreach ($imeiNumbers as $number) {
                        $key = array_search($number, $allImeiNumbers);
                        if ($key !== false) {
                            unset($allImeiNumbers[$key]);
                        }
                    }

                    $productWarehouse->imei_number = implode(',', $allImeiNumbers);
                }

                $productWarehouse->save();
            }
        }

        // Create product sale record
        ProductSale::create([
            'sale_id' => $sale->id,
            'product_id' => $product->id,
            'variant_id' => $variantId,
            'product_batch_id' => $productBatchId,
            'imei_number' => ($imeiNumber && !str_contains($imeiNumber, 'null')) ? $imeiNumber : null,
            'qty' => $qty,
            'sale_unit_id' => $saleUnitId,
            'net_unit_price' => $netUnitPrice,
            'discount' => $discount,
            'tax_rate' => $taxRate,
            'tax' => $tax,
            'total' => $total,
            'topping_id' => $toppingId,
        ]);
    }

    /**
     * Process payments for a sale.
     *
     * @param Sale $sale
     * @param array<string, mixed> $data
     * @return void
     */
    protected function processPayments(Sale $sale, array $data): void
    {
        $paidAmounts = is_array($data['paid_amount'] ?? null) ? $data['paid_amount'] : [$data['paid_amount'] ?? 0];
        $paidByIds = is_array($data['paid_by_id'] ?? null) ? $data['paid_by_id'] : [$data['paid_by_id'] ?? 'Cash'];
        $accountIds = is_array($data['account_id'] ?? null) ? $data['account_id'] : [$data['account_id'] ?? null];
        $chequeNumbers = is_array($data['cheque_number'] ?? null) ? $data['cheque_number'] : [];
        $giftCardIds = is_array($data['gift_card_id'] ?? null) ? $data['gift_card_id'] : [];
        $creditCardNumbers = is_array($data['credit_card_number'] ?? null) ? $data['credit_card_number'] : [];
        $paypalTransactions = is_array($data['paypal_transaction_id'] ?? null) ? $data['paypal_transaction_id'] : [];

        foreach ($paidAmounts as $key => $amount) {
            if ($amount <= 0) {
                continue;
            }

            $paidById = $paidByIds[$key] ?? 'Cash';
            $accountId = $accountIds[$key] ?? null;

            // Create payment
            $payment = Payment::create([
                'payment_reference' => $this->generatePaymentReference($paidById),
                'user_id' => Auth::id(),
                'sale_id' => $sale->id,
                'account_id' => $accountId ?? 0,
                'amount' => (float)$amount,
                'change' => 0,
                'paying_method' => $this->getPayingMethodName($paidById),
                'payment_note' => $data['payment_note'][$key] ?? null,
                'currency_id' => $sale->currency_id ?? 1,
                'exchange_rate' => $sale->exchange_rate ?? 1,
                'cash_register_id' => $sale->cash_register_id,
            ]);

            // Handle specific payment methods
            switch ($paidById) {
                case 'Cheque':
                    if (isset($chequeNumbers[$key])) {
                        PaymentWithCheque::create([
                            'payment_id' => $payment->id,
                            'cheque_no' => $chequeNumbers[$key],
                        ]);
                    }
                    break;

                case 'Gift Card':
                    if (isset($giftCardIds[$key])) {
                        $giftCard = GiftCard::find($giftCardIds[$key]);
                        if ($giftCard) {
                            $giftCard->expense += (float)$amount;
                            $giftCard->save();

                            PaymentWithGiftCard::create([
                                'payment_id' => $payment->id,
                                'gift_card_id' => $giftCardIds[$key],
                            ]);
                        }
                    }
                    break;

                case 'Credit Card':
                    if (isset($creditCardNumbers[$key])) {
                        PaymentWithCreditCard::create([
                            'payment_id' => $payment->id,
                            'credit_card_number' => $creditCardNumbers[$key],
                        ]);
                    }
                    break;

                case 'Paypal':
                    if (isset($paypalTransactions[$key])) {
                        PaymentWithPaypal::create([
                            'payment_id' => $payment->id,
                            'transaction_id' => $paypalTransactions[$key],
                        ]);
                    }
                    break;

                case 'Razorpay':
                    // Razorpay payment is handled separately in controller
                    break;
            }

            // Update account balance
            if ($accountId) {
                $account = Account::find($accountId);
                if ($account) {
                    $account->balance += (float)$amount;
                    $account->save();
                }
            }
        }
    }

    /**
     * Generate payment reference number.
     *
     * @param string $method
     * @return string
     */
    protected function generatePaymentReference(string $method): string
    {
        $prefix = match ($method) {
            'Razorpay' => 'raz-',
            'Paypal' => 'pp-',
            'Stripe' => 'st-',
            default => 'ppr-',
        };

        return $prefix . date('Ymd') . '-' . date('his');
    }

    /**
     * Get paying method name.
     *
     * @param string|int $paidById
     * @return string
     */
    protected function getPayingMethodName(string|int $paidById): string
    {
        $methods = [
            '1' => 'Cash',
            '2' => 'Cheque',
            '3' => 'Credit Card',
            '4' => 'Debit Card',
            '5' => 'Bank Transfer',
            '6' => 'Gift Card',
            '7' => 'Paypal',
            '8' => 'Razorpay',
            '9' => 'Stripe',
            '10' => 'Xendit',
        ];

        return $methods[(string)$paidById] ?? 'Cash';
    }

    /**
     * Handle reward points for a sale.
     *
     * @param Sale $sale
     * @param array<string, mixed> $data
     * @return void
     */
    protected function handleRewardPoints(Sale $sale, array $data): void
    {
        // Skip if redeeming points or draft
        if (isset($data['redeem_point']) || ($data['draft'] ?? false)) {
            return;
        }

        $rewardPointSetting = RewardPointSetting::latest()->first();
        if (!$rewardPointSetting || !$rewardPointSetting->is_active) {
            return;
        }

        if ($sale->grand_total < $rewardPointSetting->minimum_amount) {
            return;
        }

        $customer = Customer::find($sale->customer_id);
        if (!$customer || $customer->type !== CustomerTypeEnum::REGULAR->value) {
            return;
        }

        // Calculate points
        $points = (int)($sale->grand_total / $rewardPointSetting->per_point_amount);

        // Add points to customer
        $customer->points += $points;
        $customer->save();

        // Calculate expiration date
        $expiredAt = null;
        if ($rewardPointSetting->duration && $rewardPointSetting->type) {
            $expiredAt = match ($rewardPointSetting->type) {
                'days' => now()->addDays($rewardPointSetting->duration),
                'months' => now()->addMonths($rewardPointSetting->duration),
                'years' => now()->addYears($rewardPointSetting->duration),
                default => null,
            };
        }

        // Create reward point record
        RewardPoint::create([
            'points' => $points,
            'customer_id' => $customer->id,
            'note' => 'Earn Point for sale #' . $sale->id,
            'sale_id' => $sale->id,
            'expired_at' => $expiredAt,
        ]);
    }

    /**
     * Apply coupon to sale.
     *
     * @param int|null $couponId
     * @return void
     */
    protected function applyCoupon(?int $couponId): void
    {
        if (!$couponId) {
            return;
        }

        $coupon = Coupon::find($couponId);
        if ($coupon) {
            $coupon->used += 1;
            $coupon->save();
        }
    }

    /**
     * Send sale email notification.
     *
     * @param Sale $sale
     * @return void
     */
    protected function sendSaleEmail(Sale $sale): void
    {
        $mailSetting = MailSetting::default()->first();
        if (!$mailSetting) {
            throw ValidationException::withMessages([
                'email' => ['Mail settings are not configured. Please contact the administrator.'],
            ]);
        }

        $customer = Customer::find($sale->customer_id);
        if (!$customer || !$customer->email) {
            return;
        }

        // Prepare mail data
        $mailData = [
            'email' => $customer->email,
            'reference_no' => $sale->reference_no,
            'sale_status' => $sale->sale_status,
            'payment_status' => $sale->payment_status,
            'total_qty' => $sale->total_qty,
            'total_price' => $sale->total_price,
            'order_tax' => $sale->order_tax,
            'order_tax_rate' => $sale->order_tax_rate,
            'order_discount' => $sale->order_discount,
            'shipping_cost' => $sale->shipping_cost,
            'grand_total' => $sale->grand_total,
            'paid_amount' => $sale->paid_amount,
            'products' => [],
            'qty' => [],
            'unit' => [],
            'total' => [],
        ];

        $productSales = ProductSale::where('sale_id', $sale->id)->with('product', 'variant')->get();
        foreach ($productSales as $key => $productSale) {
            $product = $productSale->product;
            $variant = $productSale->variant;

            if ($variant) {
                $mailData['products'][$key] = $product->name . ' [' . $variant->name . ']';
            } else {
                $mailData['products'][$key] = $product->name;
            }

            $mailData['qty'][$key] = $productSale->qty;
            $mailData['total'][$key] = $productSale->total;

            if ($productSale->sale_unit_id) {
                $saleUnit = Unit::find($productSale->sale_unit_id);
                $mailData['unit'][$key] = $saleUnit ? $saleUnit->unit_code : '';
            } else {
                $mailData['unit'][$key] = '';
            }
        }

        try {
            Mail::to($customer->email)->send(new SaleDetails($mailData));
        } catch (Exception $e) {
            $this->logError('Failed to send sale email: ' . $e->getMessage(), [
                'sale_id' => $sale->id,
                'customer_email' => $customer->email,
            ]);
        }
    }

    /**
     * Create activity log for sale.
     *
     * @param Sale $sale
     * @param string $action
     * @return void
     */
    protected function createSaleActivityLog(Sale $sale, string $action): void
    {
        // Activity log implementation
        // This should integrate with your activity log system
        $this->logInfo("Sale Activity: {$action}", [
            'sale_id' => $sale->id,
            'reference_no' => $sale->reference_no,
            'user_id' => Auth::id(),
        ]);
    }

    /**
     * Create installment plan for sale.
     *
     * @param Sale $sale
     * @param array<string, mixed> $installmentData
     * @return void
     */
    protected function createInstallmentPlan(Sale $sale, array $installmentData): void
    {
        $installmentData['reference_id'] = $sale->id;
        $installmentData['reference_type'] = 'sale';

        InstallmentPlan::create($installmentData);
    }

    /**
     * Update an existing sale.
     *
     * @param Sale $sale
     * @param array<string, mixed> $data
     * @param UploadedFile|null $document
     * @return Sale
     * @throws Exception
     */
    public function updateSale(Sale $sale, array $data, ?UploadedFile $document = null): Sale
    {
        return $this->transaction(function () use ($sale, $data, $document) {
            // Validate reference number if changed
            if (isset($data['reference_no']) && $data['reference_no'] !== $sale->reference_no) {
                $this->validateReferenceNumber($data['reference_no'], $sale->id);
            }

            // Restore stock from old sale if status changed
            if ($sale->sale_status == 1 && ($data['sale_status'] ?? $sale->sale_status) != 1) {
                $this->restoreStock($sale);
            }

            // Prepare sale data
            $saleData = $this->prepareSaleData($data, $document, $sale);

            // Update sale
            $sale->update($saleData);

            // Handle custom fields
            $this->handleCustomFields($sale, $data);

            // Delete old product sales
            ProductSale::where('sale_id', $sale->id)->delete();

            // Process products and update stock
            if (($data['sale_status'] ?? $sale->sale_status) == 1) {
                $this->processSaleProducts($sale, $data);
            }

            // Update payments
            $this->updatePayments($sale, $data);

            // Create activity log
            $this->createSaleActivityLog($sale, 'Sale Updated');

            return $sale->fresh(['customer', 'warehouse', 'user', 'productSales.product']);
        });
    }

    /**
     * Restore stock for a sale (used when sale is cancelled or deleted).
     *
     * @param Sale $sale
     * @return void
     */
    protected function restoreStock(Sale $sale): void
    {
        $productSales = ProductSale::where('sale_id', $sale->id)->get();

        foreach ($productSales as $productSale) {
            $product = Product::find($productSale->product_id);
            if (!$product) {
                continue;
            }

            // Calculate quantity (considering sale unit)
            $quantity = $productSale->qty;
            if ($productSale->sale_unit_id) {
                $saleUnit = Unit::find($productSale->sale_unit_id);
                if ($saleUnit) {
                    if ($saleUnit->operator === '*') {
                        $quantity = $productSale->qty * $saleUnit->operation_value;
                    } elseif ($saleUnit->operator === '/') {
                        $quantity = $productSale->qty / $saleUnit->operation_value;
                    }
                }
            }

            // Restore product stock
            if ($product->type !== 'combo') {
                $product->qty += $quantity;
                $product->save();

                // Restore variant stock
                if ($productSale->variant_id) {
                    $productVariant = ProductVariant::where([
                        ['product_id', $product->id],
                        ['variant_id', $productSale->variant_id],
                    ])->first();

                    if ($productVariant) {
                        $productVariant->qty += $quantity;
                        $productVariant->save();
                    }
                }

                // Restore batch stock
                if ($productSale->product_batch_id) {
                    $productBatch = ProductBatch::find($productSale->product_batch_id);
                    if ($productBatch) {
                        $productBatch->qty += $quantity;
                        $productBatch->save();
                    }
                }

                // Restore warehouse stock
                $productWarehouse = ProductWarehouse::where([
                    ['product_id', $product->id],
                    ['warehouse_id', $sale->warehouse_id],
                ])->when($productSale->variant_id, function ($q) use ($productSale) {
                    $q->where('variant_id', $productSale->variant_id);
                })->when($productSale->product_batch_id, function ($q) use ($productSale) {
                    $q->where('product_batch_id', $productSale->product_batch_id);
                })->first();

                if ($productWarehouse) {
                    $productWarehouse->qty += $quantity;

                    // Restore IMEI numbers
                    if ($productSale->imei_number) {
                        $existingImei = $productWarehouse->imei_number ?? '';
                        $existingImeiArray = $existingImei ? explode(',', $existingImei) : [];
                        $newImeiArray = explode(',', $productSale->imei_number);
                        $allImei = array_merge($existingImeiArray, $newImeiArray);
                        $productWarehouse->imei_number = implode(',', array_filter($allImei));
                    }

                    $productWarehouse->save();
                }
            } else {
                // Restore combo product child stock
                $this->restoreComboProductStock($product, $quantity, $sale->warehouse_id);
            }
        }
    }

    /**
     * Restore stock for combo product children.
     *
     * @param Product $comboProduct
     * @param float $qty
     * @param int $warehouseId
     * @return void
     */
    protected function restoreComboProductStock(Product $comboProduct, float $qty, int $warehouseId): void
    {
        $productList = explode(',', $comboProduct->product_list);
        $variantList = $comboProduct->variant_list ? explode(',', $comboProduct->variant_list) : [];
        $qtyList = explode(',', $comboProduct->qty_list);

        foreach ($productList as $key => $childId) {
            $childProduct = Product::find($childId);
            if (!$childProduct) {
                continue;
            }

            $childQty = $qty * (float)($qtyList[$key] ?? 1);

            $childProduct->qty += $childQty;
            $childProduct->save();

            if (isset($variantList[$key]) && $variantList[$key]) {
                $childVariant = ProductVariant::where([
                    ['product_id', $childId],
                    ['variant_id', $variantList[$key]],
                ])->first();

                if ($childVariant) {
                    $childVariant->qty += $childQty;
                    $childVariant->save();
                }

                $childWarehouse = ProductWarehouse::where([
                    ['product_id', $childId],
                    ['variant_id', $variantList[$key]],
                    ['warehouse_id', $warehouseId],
                ])->first();
            } else {
                $childWarehouse = ProductWarehouse::where([
                    ['product_id', $childId],
                    ['warehouse_id', $warehouseId],
                ])->whereNull('variant_id')
                    ->first();
            }

            if ($childWarehouse) {
                $childWarehouse->qty += $childQty;
                $childWarehouse->save();
            }
        }
    }

    /**
     * Update payments for a sale.
     *
     * @param Sale $sale
     * @param array<string, mixed> $data
     * @return void
     */
    protected function updatePayments(Sale $sale, array $data): void
    {
        // Delete old payments
        Payment::where('sale_id', $sale->id)->delete();

        // Create new payments
        $this->processPayments($sale, $data);
    }

    /**
     * Delete a sale (soft delete).
     *
     * @param Sale $sale
     * @return bool
     * @throws Exception
     */
    public function deleteSale(Sale $sale): bool
    {
        return $this->transaction(function () use ($sale) {
            // Restore stock if sale was completed
            if ($sale->sale_status == 1) {
                $this->restoreStock($sale);
            }

            // Delete related records
            ProductSale::where('sale_id', $sale->id)->delete();
            Payment::where('sale_id', $sale->id)->delete();
            PaymentWithCheque::where('payment_id', function ($query) use ($sale) {
                $query->select('id')
                    ->from('payments')
                    ->where('sale_id', $sale->id);
            })->delete();
            PaymentWithCreditCard::where('payment_id', function ($query) use ($sale) {
                $query->select('id')
                    ->from('payments')
                    ->where('sale_id', $sale->id);
            })->delete();
            PaymentWithGiftCard::where('payment_id', function ($query) use ($sale) {
                $query->select('id')
                    ->from('payments')
                    ->where('sale_id', $sale->id);
            })->delete();
            PaymentWithPaypal::where('payment_id', function ($query) use ($sale) {
                $query->select('id')
                    ->from('payments')
                    ->where('sale_id', $sale->id);
            })->delete();

            // Soft delete sale
            $sale->delete();

            // Create activity log
            $this->createSaleActivityLog($sale, 'Sale Deleted');

            return true;
        });
    }

    /**
     * Get sales with filters and pagination.
     *
     * @param array<string, mixed> $filters
     * @return array<string, mixed>
     */
    public function getSales(array $filters = []): array
    {
        $query = Sale::with(['customer', 'warehouse', 'user'])
            ->whereNull('deleted_at');

        // Apply filters
        if (isset($filters['warehouse_id']) && $filters['warehouse_id'] > 0) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (isset($filters['sale_status']) && $filters['sale_status'] > 0) {
            $query->where('sale_status', $filters['sale_status']);
        }

        if (isset($filters['payment_status']) && $filters['payment_status'] > 0) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (isset($filters['sale_type']) && $filters['sale_type'] > 0) {
            $query->where('sale_type', $filters['sale_type']);
        }

        if (isset($filters['starting_date']) && isset($filters['ending_date'])) {
            $query->whereBetween('created_at', [
                $filters['starting_date'],
                $filters['ending_date'] . ' 23:59:59',
            ]);
        }

        // Apply search
        if (isset($filters['search']) && !empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('reference_no', 'LIKE', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('phone', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Get total count
        $total = $query->count();

        // Apply pagination
        if (isset($filters['limit'])) {
            $query->limit((int)$filters['limit']);
        }
        if (isset($filters['offset'])) {
            $query->offset((int)$filters['offset']);
        }

        // Apply ordering
        $orderBy = $filters['order_by'] ?? 'created_at';
        $orderDir = $filters['order_dir'] ?? 'desc';
        $query->orderBy($orderBy, $orderDir);

        $sales = $query->get();

        return [
            'data' => $sales,
            'total' => $total,
        ];
    }

    /**
     * Get sale by ID with all relationships.
     *
     * @param int $id
     * @return Sale|null
     */
    public function getSale(int $id): ?Sale
    {
        return Sale::with([
            'customer',
            'warehouse',
            'user',
            'productSales.product',
            'productSales.variant',
            'productSales.productBatch',
            'payments',
        ])->find($id);
    }
}


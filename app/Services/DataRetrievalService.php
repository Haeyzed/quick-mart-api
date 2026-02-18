<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Account;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DiscountPlan;
use App\Models\ExpenseCategory;
use App\Models\ExternalService;
use App\Models\HrmSetting;
use App\Models\IncomeCategory;
use App\Models\MailSetting;
use App\Models\PosSetting;
use App\Models\Tax;
use App\Models\Unit;
use App\Models\Warehouse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;

/**
 * Data Retrieval Service
 *
 * Service for retrieving common reference data used throughout the application.
 * This service provides centralized access to lists, settings, and configuration data.
 *
 * @package App\Services
 */
class DataRetrievalService
{
    /**
     * Get all active units.
     *
     * @return Collection<Unit> Collection of active units
     * @throws AuthorizationException If user lacks permission
     */
    public function getAllUnits()
    {
        $user = Auth::user();
        if (!$user || !$user->hasPermissionTo('unit')) {
            throw new AuthorizationException('Unauthorized access to units.');
        }

        return Unit::where('is_active', true)->get();
    }

    /**
     * Get all active brands.
     *
     * @return Collection<Brand> Collection of active brands
     */
    public function getAllBrands()
    {
        return Brand::where('is_active', true)->get();
    }

    /**
     * Get all active discount plans.
     *
     * @return Collection<DiscountPlan> Collection of active discount plans
     */
    public function getDiscountPlans()
    {
        return DiscountPlan::where('is_active', true)->get();
    }

    /**
     * Get all active warehouses.
     *
     * @return Collection<Warehouse> Collection of active warehouses
     */
    public function getAllWarehouses()
    {
        return Warehouse::where('is_active', true)->get();
    }

    /**
     * Get all active income categories.
     *
     * @return Collection<IncomeCategory> Collection of active income categories
     */
    public function getAllIncomeCategories()
    {
        return IncomeCategory::where('is_active', true)->get();
    }

    /**
     * Get all active expense categories.
     *
     * @return Collection<ExpenseCategory> Collection of active expense categories
     */
    public function getAllExpenseCategories()
    {
        return ExpenseCategory::where('is_active', true)->get();
    }

    /**
     * Get all active accounts.
     *
     * @return Collection<Account> Collection of active accounts
     */
    public function getAllAccounts()
    {
        return Account::where('is_active', true)->get();
    }

    /**
     * Get all active categories.
     *
     * @return Collection<Category> Collection of active categories
     * @throws AuthorizationException If user lacks permission
     */
    public function getAllCategories()
    {
        $user = Auth::user();
        if (!$user || !$user->hasPermissionTo('category')) {
            throw new AuthorizationException('Sorry! You are not allowed to access this module.');
        }

        return Category::where('is_active', true)
            ->orderBy('id', 'desc')
            ->get();
    }

    /**
     * Get HRM settings.
     *
     * @return HrmSetting|null Latest HRM settings or null if not found
     */
    public function hrmSetting(): ?HrmSetting
    {
        return HrmSetting::latest()->first();
    }

    /**
     * Get POS settings.
     *
     * @return array<string, mixed> POS settings with payment options array
     */
    public function posSetting(): array
    {
        $posSetting = PosSetting::latest()->first();

        if (!$posSetting) {
            return ['options' => []];
        }

        $options = !empty($posSetting->payment_options)
            ? explode(',', $posSetting->payment_options)
            : [];

        return array_merge($posSetting->toArray(), ['options' => $options]);
    }

    /**
     * Get mail settings.
     *
     * @return MailSetting|null Default mail settings or null if not found
     */
    public function mailSetting(): ?MailSetting
    {
        return MailSetting::default()->first();
    }

    /**
     * Get payment gateways.
     *
     * @return Collection<ExternalService> Collection of active payment gateways
     */
    public function paymentGateways()
    {
        return ExternalService::where('type', 'payment')
            ->where('active', true)
            ->get();
    }

    /**
     * Get product types.
     *
     * @return array<int, array<string, string>> Array of product type options
     */
    public function getProductTypes(): array
    {
        return [
            ['label' => 'Standard', 'value' => 'standard'],
            ['label' => 'Combo', 'value' => 'combo'],
            ['label' => 'Digital', 'value' => 'digital'],
            ['label' => 'Service', 'value' => 'service'],
        ];
    }

    /**
     * Get barcode symbologies.
     *
     * @return array<int, array<string, string>> Array of barcode symbology options
     */
    public function getBarcodeSymbologies(): array
    {
        return [
            ['label' => 'Code 128', 'value' => 'C128'],
            ['label' => 'Code 39', 'value' => 'C39'],
            ['label' => 'UPC-A', 'value' => 'UPCA'],
            ['label' => 'UPC-E', 'value' => 'UPCE'],
            ['label' => 'EAN-8', 'value' => 'EAN8'],
            ['label' => 'EAN-13', 'value' => 'EAN13'],
        ];
    }

    /**
     * Get tax methods.
     *
     * @return array<int, array<string, string>> Array of tax method options
     */
    public function getTaxMethods(): array
    {
        return [
            ['label' => 'Exclusive', 'value' => '1'],
            ['label' => 'Inclusive', 'value' => '2'],
        ];
    }

    /**
     * Get purchase status options.
     *
     * @return array<int, array<string, string>> Array of purchase status options
     */
    public function getPurchaseStatus(): array
    {
        return [
            ['label' => 'Received', 'value' => '1'],
            ['label' => 'Partial', 'value' => '2'],
            ['label' => 'Pending', 'value' => '3'],
            ['label' => 'Ordered', 'value' => '4'],
        ];
    }

    /**
     * Get sale status options.
     *
     * @return array<int, array<string, string>> Array of sale status options
     */
    public function getSaleStatus(): array
    {
        return [
            ['label' => 'Completed', 'value' => '1'],
            ['label' => 'Pending', 'value' => '2'],
        ];
    }

    /**
     * Get sale payment status options.
     *
     * @return array<int, array<string, string>> Array of sale payment status options
     */
    public function getSalePaymentStatus(): array
    {
        return [
            ['label' => 'Pending', 'value' => '1'],
            ['label' => 'Due', 'value' => '2'],
            ['label' => 'Partial', 'value' => '3'],
            ['label' => 'Paid', 'value' => '4'],
        ];
    }

    /**
     * Get product taxes.
     *
     * @return Collection<Tax> Collection of active taxes formatted for select options
     */
    public function getProductTaxes()
    {
        return Tax::where('is_active', true)
            ->get()
            ->map(function (Tax $tax) {
                return [
                    'label' => $tax->name,
                    'value' => (string)$tax->rate,
                ];
            });
    }

    /**
     * Get warranty type options.
     *
     * @return array<int, array<string, string>> Array of warranty type options
     */
    public function getWarrantyType(): array
    {
        return [
            ['label' => 'Days', 'value' => 'days'],
            ['label' => 'Months', 'value' => 'months'],
            ['label' => 'Years', 'value' => 'years'],
        ];
    }

    /**
     * Get order discount type options.
     *
     * @return array<int, array<string, string>> Array of order discount type options
     */
    public function getOrderDiscountType(): array
    {
        return [
            ['label' => 'Flat', 'value' => 'Flat'],
            ['label' => 'Percentage', 'value' => 'Percentage'],
        ];
    }

    /**
     * Get discount types.
     *
     * @return array<int, array<string, string>> Array of discount type options
     */
    public function getDiscountTypes(): array
    {
        return [
            ['label' => 'Percentage (%)', 'value' => 'percentage'],
            ['label' => 'Flat', 'value' => 'flat'],
        ];
    }

    /**
     * Get discount applicable options.
     *
     * @return array<int, array<string, string>> Array of discount applicable options
     */
    public function getDiscountApplicable(): array
    {
        return [
            ['label' => 'All Products', 'value' => 'All'],
            ['label' => 'Specific Products', 'value' => 'Specific'],
        ];
    }

    /**
     * Get week days.
     *
     * @return array<int, array<string, string>> Array of week day options
     */
    public function weekDays(): array
    {
        return [
            ['label' => 'Monday', 'value' => 'Mon'],
            ['label' => 'Tuesday', 'value' => 'Tue'],
            ['label' => 'Wednesday', 'value' => 'Wed'],
            ['label' => 'Thursday', 'value' => 'Thu'],
            ['label' => 'Friday', 'value' => 'Fri'],
            ['label' => 'Saturday', 'value' => 'Sat'],
            ['label' => 'Sunday', 'value' => 'Sun'],
        ];
    }

    /**
     * Get account types.
     *
     * @return array<int, array<string, string>> Array of account type options
     */
    public function accountTypes(): array
    {
        return [
            ['label' => 'All', 'value' => '0'],
            ['label' => 'Debit', 'value' => '1'],
            ['label' => 'Credit', 'value' => '2'],
        ];
    }
}


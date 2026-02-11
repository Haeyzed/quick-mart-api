<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Support;

/**
 * Resolves permission names to their module for grouping in the UI.
 */
final class PermissionModuleResolver
{
    private const REPORT_PERMISSIONS = [
        'product-report', 'purchase-report', 'sale-report', 'customer-report', 'customer-group-report',
        'due-report', 'payment-report', 'warehouse-stock-report', 'product-qty-alert', 'supplier-report',
        'profit-loss', 'best-seller', 'daily-sale', 'monthly-sale', 'daily-purchase', 'monthly-purchase',
        'audit-logs-index', 'audit-logs-export', 'user-report', 'warehouse-report', 'yearly_report',
        'product-expiry-report', 'sale-report-chart', 'dso-report', 'supplier-due-report',
        'biller-report', 'sidebar_reports', 'packing_slip_challan',
    ];

    /**
     * Module mapping: module => [prefixes, exact matches].
     *
     * @var array<string, array{0: array<string>, 1: array<string>}>
     */
    private const MODULE_MAP = [
        'products' => [['products-'], []],
        'purchases' => [['purchases-', 'purchase-return-', 'purchase-payment-'], ['purchase_export']],
        'sales' => [['sales-', 'sale-payment-'], ['sale_export', 'sale-agents']],
        'returns' => [['returns-'], []],
        'transfers' => [['transfers-'], []],
        'quotations' => [['quotes-'], []],
        'customers' => [['customers-'], []],
        'customer_groups' => [['customer-groups-'], ['customer_group']],
        'suppliers' => [['suppliers-'], []],
        'taxes' => [['taxes-'], []],
        'units' => [['units-'], []],
        'categories' => [['categories-'], ['category']],
        'brands' => [['brands-'], []],
        'warehouses' => [['warehouses-'], ['warehouse']],
        'billers' => [['billers-'], []],
        'users' => [['users-'], []],
        'expenses' => [['expenses-'], []],
        'incomes' => [['incomes-'], []],
        'accounts' => [['account-'], ['account-index', 'balance-sheet', 'account-statement', 'account-selection']],
        'hrm' => [['employees-'], ['department', 'attendance', 'payroll', 'designations', 'shift', 'overtime', 'leave-type', 'leave', 'hrm-panel']],
        'settings' => [[], ['general_setting', 'mail_setting', 'pos_setting', 'hrm_setting', 'sms_setting', 'create_sms', 'payment_gateway_setting', 'barcode_setting', 'language_setting', 'reward_point_setting']],
        'dashboard' => [[], ['today_sale', 'today_profit', 'revenue_profit_summary', 'cash_flow', 'monthly_summary']],
        'sidebar' => [[], ['sidebar_product', 'sidebar_purchase', 'sidebar_sale', 'sidebar_quotation', 'sidebar_transfer', 'sidebar_expense', 'sidebar_income', 'sidebar_accounting', 'sidebar_hrm', 'sidebar_people', 'sidebar_settings']],
    ];

    public static function resolve(string $permissionName): string
    {
        if (in_array($permissionName, self::REPORT_PERMISSIONS, true)) {
            return 'reports';
        }

        foreach (self::MODULE_MAP as $module => [$prefixes, $exact]) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with($permissionName, $prefix)) {
                    return $module;
                }
            }
            if (in_array($permissionName, $exact, true)) {
                return $module;
            }
        }

        return 'other';
    }
}

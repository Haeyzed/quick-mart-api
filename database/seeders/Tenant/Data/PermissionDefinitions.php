<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant\Data;

/**
 * Permission definitions for tenant seeding.
 *
 * Centralizes permission names and guard to avoid duplication.
 */
final class PermissionDefinitions
{
    private const GUARD = 'web';

    /**
     * All permissions to seed.
     *
     * @return array<int, array{name: string, guard_name: string}>
     */
    public static function all(): array
    {
        return array_map(
            fn (string $name) => ['name' => $name, 'guard_name' => self::GUARD],
            self::PERMISSION_NAMES
        );
    }

    /**
     * Admin role gets all permissions when not in SaaS mode.
     *
     * @return array<int, array{permission: string, role: string}>
     */
    public static function adminMappings(): array
    {
        return array_map(
            fn (string $name) => ['permission' => $name, 'role' => 'Admin'],
            self::PERMISSION_NAMES
        );
    }

    /**
     * Basic permissions for SaaS package (subset).
     *
     * @return array<int, array{permission: string, role: string}>
     */
    public static function basicSaaSMappings(): array
    {
        $basic = [
            'products-index', 'products-add', 'products-edit', 'products-delete',
            'purchases-index', 'purchases-add', 'purchases-edit', 'purchases-delete',
            'sales-index', 'sales-add', 'sales-edit', 'sales-delete',
            'returns-index', 'returns-add', 'returns-edit', 'returns-delete',
            'customers-index', 'customers-add', 'customers-edit', 'customers-delete',
            'suppliers-index', 'suppliers-add', 'suppliers-edit', 'suppliers-delete',
            'product-report', 'purchase-report', 'sale-report', 'customer-report', 'customer-group-report', 'due-report',
            'payment-report', 'warehouse-stock-report', 'product-qty-alert', 'supplier-report',
            'profit-loss', 'best-seller', 'daily-sale', 'monthly-sale', 'daily-purchase', 'monthly-purchase',
            'audit-logs-index', 'audit-logs-export', 'user-report', 'warehouse-report',
            'product-expiry-report', 'sale-report-chart', 'dso-report', 'supplier-due-report',
            'biller-report', 'general_setting', 'mail_setting', 'pos_setting',
            'users-index', 'users-create', 'users-update', 'users-delete',
            'warehouses-index', 'billers-index', 'expenses-index', 'incomes-index',
            'today_sale', 'today_profit', 'sidebar_reports', 'packing_slip_challan',
        ];

        return array_map(
            fn (string $name) => ['permission' => $name, 'role' => 'Admin'],
            $basic
        );
    }

    /**
     * @var array<int, string>
     */
    private const PERMISSION_NAMES = [
        'products-index', 'products-add', 'products-edit', 'products-delete',
        'purchases-index', 'purchases-add', 'purchases-edit', 'purchases-delete',
        'sales-index', 'sales-add', 'sales-edit', 'sales-delete',
        'returns-index', 'returns-add', 'returns-edit', 'returns-delete',
        'transfers-index', 'transfers-add', 'transfers-edit', 'transfers-delete',
        'quotes-index', 'quotes-add', 'quotes-edit', 'quotes-delete',
        'customers-index', 'customers-add', 'customers-edit', 'customers-delete',
        'suppliers-index', 'suppliers-add', 'suppliers-edit', 'suppliers-delete',
        'product-report', 'purchase-report', 'sale-report', 'customer-report', 'customer-group-report', 'due-report',
        'payment-report', 'warehouse-stock-report', 'product-qty-alert', 'supplier-report',
        'profit-loss', 'best-seller', 'daily-sale', 'monthly-sale', 'daily-purchase', 'monthly-purchase',
        'audit-logs-index', 'audit-logs-export', 'user-report', 'warehouse-report',
        'product-expiry-report', 'sale-report-chart', 'dso-report', 'supplier-due-report',
        'biller-report', 'sidebar_reports', 'packing_slip_challan',
        'users-index', 'users-create', 'users-update', 'users-delete',
        'expenses-index', 'expenses-create', 'expenses-update', 'expenses-delete',
        'general_setting', 'mail_setting', 'pos_setting', 'hrm_setting', 'sms_setting', 'create_sms',
        'payment_gateway_setting', 'barcode_setting', 'language_setting', 'reward_point_setting',
        'purchase-return-index', 'purchase-return-create', 'purchase-return-update', 'purchase-return-delete',
        'account-index', 'balance-sheet', 'account-statement', 'account-selection',
        'department', 'attendance', 'payroll',
        'employees-index', 'employees-create', 'employees-update', 'employees-delete',
        'stock_count', 'adjustment', 'empty_database',
        'customer_group', 'gift_card', 'coupon', 'holiday',
        'warehouse', 'warehouses-index',
        'customer-groups-index', 'customer-groups-create', 'customer-groups-update', 'customer-groups-delete',
        'customer-groups-import', 'customer-groups-export',
        'designations', 'shift', 'overtime', 'leave-type', 'leave', 'hrm-panel',
        'sale-payment-index', 'sale-payment-create', 'sale-payment-update', 'sale-payment-delete',
        'all_notification', 'product_history', 'custom_field',
        'incomes-index', 'incomes-create', 'incomes-update', 'incomes-delete',
        'payment_gateway_setting', 'barcode_setting', 'language_setting',
        'account-selection', 'invoice_setting', 'invoice_create_edit_delete', 'handle_discount',
        'purchases-import', 'sales-import', 'customers-import', 'billers-import',
        'role_permission', 'cart-product-update',
    ];
}

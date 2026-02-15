<?php

use App\Http\Controllers\Api\AppSettingController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BillerController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\CreateSmsController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\CustomerGroupController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DesignationController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\DiscountPlanController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\GeneralSettingController;
use App\Http\Controllers\Api\GiftCardController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\HrmSettingController;
use App\Http\Controllers\Api\ImportTemplateController;
use App\Http\Controllers\Api\IncomeCategoryController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\MailSettingController;
use App\Http\Controllers\Api\PaymentGatewaySettingController;
use App\Http\Controllers\Api\PosSettingController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\RewardPointSettingController;
use App\Http\Controllers\Api\SaleAgentController;
use App\Http\Controllers\Api\SmsSettingController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\UtilityController;
use App\Http\Controllers\Api\VariantController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Support\Facades\Route;

// Authentication routes (public)
Route::post('auth/login', [AuthController::class, 'login'])->name('auth.login');
Route::post('auth/register', [AuthController::class, 'register'])->name('auth.register');
Route::post('auth/forgot-password', [AuthController::class, 'forgotPassword'])->name('auth.forgot-password');
Route::post('auth/reset-password', [AuthController::class, 'resetPassword'])->name('auth.reset-password');
Route::get('auth/verify-email/{id}/{hash}', [AuthController::class, 'verifyEmail'])
    ->middleware(['signed'])
    ->name('verification.verify');

// Social OAuth routes (public)
Route::get('auth/google', [SocialAuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('auth/google/callback', [SocialAuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('auth/facebook', [SocialAuthController::class, 'redirectToFacebook'])->name('auth.facebook');
Route::get('auth/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback'])->name('auth.facebook.callback');
Route::get('auth/github', [SocialAuthController::class, 'redirectToGithub'])->name('auth.github');
Route::get('auth/github/callback', [SocialAuthController::class, 'handleGithubCallback'])->name('auth.github.callback');

// Authenticated routes - All API routes require authentication
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes that require authentication
    Route::get('auth/user', [AuthController::class, 'user'])->name('auth.user');
    Route::put('auth/profile', [AuthController::class, 'updateProfile'])->name('auth.update-profile');
    Route::post('auth/change-password', [AuthController::class, 'changePassword'])->name('auth.change-password');
    Route::post('auth/unlock', [AuthController::class, 'unlock'])->name('auth.unlock');
    Route::post('auth/refresh-token', [AuthController::class, 'refreshToken'])->name('auth.refresh-token');
    Route::post('auth/logout', [AuthController::class, 'logout'])->name('auth.logout');
    Route::post('auth/logout-all', [AuthController::class, 'logoutAll'])->name('auth.logout-all');
    Route::post('auth/resend-verification-email', [AuthController::class, 'resendVerificationEmail'])
        ->name('verification.resend');

    // Users
    Route::apiResource('users', UserController::class);

    // All other API routes require authentication

    // Brand Routes
    Route::prefix('brands')->name('brands.')->group(function () {
        Route::post('bulk-destroy', [BrandController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('bulk-activate', [BrandController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('bulk-deactivate', [BrandController::class, 'bulkDeactivate'])->name('bulk-deactivate');
        Route::post('import', [BrandController::class, 'import'])->name('import');
        Route::post('export', [BrandController::class, 'export'])->name('export');
        Route::get('download', [BrandController::class, 'download'])->name('download');
        Route::get('options', [BrandController::class, 'options'])->name('options');
    });
    Route::apiResource('brands', BrandController::class);

// Warehouse Routes (Revamped to follow Brand pattern)
    Route::prefix('warehouses')->name('warehouses.')->group(function () {
        Route::post('bulk-destroy', [WarehouseController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('bulk-activate', [WarehouseController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('bulk-deactivate', [WarehouseController::class, 'bulkDeactivate'])->name('bulk-deactivate');
        Route::post('import', [WarehouseController::class, 'import'])->name('import');
        Route::post('export', [WarehouseController::class, 'export'])->name('export');
        Route::get('download', [WarehouseController::class, 'download'])->name('download');
        Route::get('options', [WarehouseController::class, 'options'])->name('options');
    });
    Route::apiResource('warehouses', WarehouseController::class);

    Route::prefix('categories')->name('categories.')->group(function () {
        Route::post('bulk-destroy', [CategoryController::class, 'bulkDestroy'])->name('bulk-destroy');
        Route::post('bulk-activate', [CategoryController::class, 'bulkActivate'])->name('bulk-activate');
        Route::post('bulk-deactivate', [CategoryController::class, 'bulkDeactivate'])->name('bulk-deactivate');
        Route::post('bulk-enable-featured', [CategoryController::class, 'bulkEnableFeatured'])->name('bulk-enable-featured');
        Route::post('bulk-disable-featured', [CategoryController::class, 'bulkDisableFeatured'])->name('bulk-disable-featured');
        Route::post('bulk-enable-sync', [CategoryController::class, 'bulkEnableSync'])->name('bulk-enable-sync');
        Route::post('bulk-disable-sync', [CategoryController::class, 'bulkDisableSync'])->name('bulk-disable-sync');
        Route::post('import', [CategoryController::class, 'import'])->name('import');
        Route::post('export', [CategoryController::class, 'export'])->name('export');
        Route::get('download', [CategoryController::class, 'download'])->name('download');
        Route::get('parents', [CategoryController::class, 'parents'])->name('parents');
        Route::get('tree', [CategoryController::class, 'tree']);
        Route::patch('{category}/reparent', [CategoryController::class, 'reparent'])->name('reparent');
    });
    Route::apiResource('categories', CategoryController::class);

    // People: Billers (under people prefix, Warehouse-style CRUD + import + bulk delete)
    Route::prefix('people')->group(function () {
        Route::get('billers/all/active', [BillerController::class, 'getAllActive'])
            ->name('billers.all-active');
        Route::patch('billers/bulk-activate', [BillerController::class, 'bulkActivate'])
            ->name('billers.bulkActivate');
        Route::patch('billers/bulk-deactivate', [BillerController::class, 'bulkDeactivate'])
            ->name('billers.bulkDeactivate');
        Route::delete('billers/bulk-destroy', [BillerController::class, 'bulkDestroy'])
            ->name('billers.bulkDestroy');
        Route::post('billers/import', [BillerController::class, 'import'])
            ->name('billers.import');
        Route::post('billers/export', [BillerController::class, 'export'])
            ->name('billers.export');
        Route::apiResource('billers', BillerController::class);
    });

    Route::get('units/base-units', [UnitController::class, 'getBaseUnits'])
        ->name('units.baseUnits');
    Route::patch('units/bulk-activate', [UnitController::class, 'bulkActivate'])
        ->name('units.bulkActivate');
    Route::patch('units/bulk-deactivate', [UnitController::class, 'bulkDeactivate'])
        ->name('units.bulkDeactivate');
    Route::delete('units/bulk-destroy', [UnitController::class, 'bulkDestroy'])
        ->name('units.bulkDestroy');
    Route::post('units/import', [UnitController::class, 'import'])
        ->name('units.import');
    Route::post('units/export', [UnitController::class, 'export'])
        ->name('units.export');
    Route::apiResource('units', UnitController::class);

    Route::patch('taxes/bulk-activate', [TaxController::class, 'bulkActivate'])
        ->name('taxes.bulkActivate');
    Route::patch('taxes/bulk-deactivate', [TaxController::class, 'bulkDeactivate'])
        ->name('taxes.bulkDeactivate');
    Route::delete('taxes/bulk-destroy', [TaxController::class, 'bulkDestroy'])
        ->name('taxes.bulkDestroy');
    Route::post('taxes/import', [TaxController::class, 'import'])
        ->name('taxes.import');
    Route::post('taxes/export', [TaxController::class, 'export'])
        ->name('taxes.export');
    Route::apiResource('taxes', TaxController::class);

    Route::patch('warehouses/bulk-activate', [WarehouseController::class, 'bulkActivate'])
        ->name('warehouses.bulkActivate');
    Route::patch('warehouses/bulk-deactivate', [WarehouseController::class, 'bulkDeactivate'])
        ->name('warehouses.bulkDeactivate');
    Route::delete('warehouses/bulk-destroy', [WarehouseController::class, 'bulkDestroy'])
        ->name('warehouses.bulkDestroy');
    Route::post('warehouses/import', [WarehouseController::class, 'import'])
        ->name('warehouses.import');
    Route::post('warehouses/export', [WarehouseController::class, 'export'])
        ->name('warehouses.export');
    Route::get('warehouses/all/active', [WarehouseController::class, 'getAllActive'])
        ->name('warehouses.all-active');
    Route::apiResource('warehouses', WarehouseController::class);

    Route::get('reports/audit-logs', [ReportController::class, 'auditLogIndex'])
        ->name('reports.audit-logs.index');
    Route::post('reports/audit-logs/export', [ReportController::class, 'auditLogExport'])
        ->name('reports.audit-logs.export');
    Route::get('reports/audit-logs/auditable-models', [UtilityController::class, 'auditableModels'])
        ->name('reports.audit-logs.auditable-models');

    Route::get('reports/product-qty-alert', [ReportController::class, 'productQtyAlert'])
        ->name('reports.product-qty-alert');
    Route::post('reports/product-qty-alert/export', [ReportController::class, 'exportProductQtyAlert'])
        ->name('reports.product-qty-alert.export');
    Route::get('reports/product-expiry', [ReportController::class, 'productExpiry'])
        ->name('reports.product-expiry');
    Route::post('reports/product-expiry/export', [ReportController::class, 'exportProductExpiry'])
        ->name('reports.product-expiry.export');
    Route::get('reports/warehouse-stock', [ReportController::class, 'warehouseStock'])
        ->name('reports.warehouse-stock');
    Route::post('reports/warehouse-stock/export', [ReportController::class, 'exportWarehouseStock'])
        ->name('reports.warehouse-stock.export');
    Route::get('reports/daily-sale', [ReportController::class, 'dailySale'])
        ->name('reports.daily-sale');
    Route::post('reports/daily-sale/export', [ReportController::class, 'exportDailySale'])
        ->name('reports.daily-sale.export');
    Route::get('reports/monthly-sale', [ReportController::class, 'monthlySale'])
        ->name('reports.monthly-sale');
    Route::post('reports/monthly-sale/export', [ReportController::class, 'exportMonthlySale'])
        ->name('reports.monthly-sale.export');
    Route::get('reports/daily-purchase', [ReportController::class, 'dailyPurchase'])
        ->name('reports.daily-purchase');
    Route::post('reports/daily-purchase/export', [ReportController::class, 'exportDailyPurchase'])
        ->name('reports.daily-purchase.export');
    Route::get('reports/monthly-purchase', [ReportController::class, 'monthlyPurchase'])
        ->name('reports.monthly-purchase');
    Route::post('reports/monthly-purchase/export', [ReportController::class, 'exportMonthlyPurchase'])
        ->name('reports.monthly-purchase.export');
    Route::get('reports/best-seller', [ReportController::class, 'bestSeller'])
        ->name('reports.best-seller');
    Route::post('reports/best-seller/export', [ReportController::class, 'exportBestSeller'])
        ->name('reports.best-seller.export');
    Route::get('reports/sale', [ReportController::class, 'saleReport'])
        ->name('reports.sale');
    Route::post('reports/sale/export', [ReportController::class, 'exportSaleReport'])
        ->name('reports.sale.export');
    Route::get('reports/purchase', [ReportController::class, 'purchaseReport'])
        ->name('reports.purchase');
    Route::post('reports/purchase/export', [ReportController::class, 'exportPurchaseReport'])
        ->name('reports.purchase.export');
    Route::get('reports/payment', [ReportController::class, 'paymentReport'])
        ->name('reports.payment');
    Route::post('reports/payment/export', [ReportController::class, 'exportPaymentReport'])
        ->name('reports.payment.export');
    Route::get('reports/supplier-due', [ReportController::class, 'supplierDueReport'])
        ->name('reports.supplier-due');
    Route::post('reports/supplier-due/export', [ReportController::class, 'exportSupplierDueReport'])
        ->name('reports.supplier-due.export');
    Route::get('reports/challan', [ReportController::class, 'challanReport'])
        ->name('reports.challan');
    Route::post('reports/challan/export', [ReportController::class, 'exportChallanReport'])
        ->name('reports.challan.export');
    Route::get('reports/product', [ReportController::class, 'productReport'])
        ->name('reports.product');
    Route::post('reports/product/export', [ReportController::class, 'exportProductReport'])
        ->name('reports.product.export');
    Route::get('reports/customer', [ReportController::class, 'customerReport'])
        ->name('reports.customer');
    Route::post('reports/customer/export', [ReportController::class, 'exportCustomerReport'])
        ->name('reports.customer.export');
    Route::get('reports/customer-group', [ReportController::class, 'customerGroupReport'])
        ->name('reports.customer-group');
    Route::post('reports/customer-group/export', [ReportController::class, 'exportCustomerGroupReport'])
        ->name('reports.customer-group.export');
    Route::get('reports/supplier', [ReportController::class, 'supplierReport'])
        ->name('reports.supplier');
    Route::post('reports/supplier/export', [ReportController::class, 'exportSupplierReport'])
        ->name('reports.supplier.export');
    Route::get('reports/user', [ReportController::class, 'userReport'])
        ->name('reports.user');
    Route::post('reports/user/export', [ReportController::class, 'exportUserReport'])
        ->name('reports.user.export');
    Route::get('reports/biller', [ReportController::class, 'billerReport'])
        ->name('reports.biller');
    Route::post('reports/biller/export', [ReportController::class, 'exportBillerReport'])
        ->name('reports.biller.export');
    Route::get('reports/warehouse', [ReportController::class, 'warehouseReport'])
        ->name('reports.warehouse');
    Route::post('reports/warehouse/export', [ReportController::class, 'exportWarehouseReport'])
        ->name('reports.warehouse.export');
    Route::get('reports/profit-loss', [ReportController::class, 'profitLoss'])
        ->name('reports.profit-loss');
    Route::post('reports/profit-loss/export', [ReportController::class, 'exportProfitLoss'])
        ->name('reports.profit-loss.export');
    Route::get('reports/sale-chart', [ReportController::class, 'saleReportChart'])
        ->name('reports.sale-chart');
    Route::post('reports/sale-chart/export', [ReportController::class, 'exportSaleReportChart'])
        ->name('reports.sale-chart.export');
    Route::get('reports/daily-sale-objective', [ReportController::class, 'dailySaleObjective'])
        ->name('reports.daily-sale-objective');
    Route::post('reports/daily-sale-objective/export', [ReportController::class, 'exportDailySaleObjective'])
        ->name('reports.daily-sale-objective.export');

    Route::get('settings/general', [GeneralSettingController::class, 'show'])
        ->name('settings.general.show');
    Route::put('settings/general', [GeneralSettingController::class, 'update'])
        ->name('settings.general.update');

    Route::get('settings/app', [AppSettingController::class, 'show'])
        ->name('settings.app.show');
    Route::delete('settings/app/tokens/{id}', [AppSettingController::class, 'destroyToken'])
        ->name('settings.app.tokens.destroy');

    Route::get('settings/mail', [MailSettingController::class, 'show'])
        ->name('settings.mail.show');
    Route::put('settings/mail', [MailSettingController::class, 'update'])
        ->name('settings.mail.update');
    Route::post('settings/mail/test', [MailSettingController::class, 'test'])
        ->name('settings.mail.test');

    Route::get('settings/sms', [SmsSettingController::class, 'index'])
        ->name('settings.sms.index');
    Route::get('settings/sms/{id}', [SmsSettingController::class, 'show'])
        ->name('settings.sms.show');
    Route::put('settings/sms/{id}', [SmsSettingController::class, 'update'])
        ->name('settings.sms.update');

    Route::get('sms/templates', [CreateSmsController::class, 'index'])
        ->name('sms.templates.index');
    Route::post('sms/send', [CreateSmsController::class, 'send'])
        ->name('sms.send');

    Route::get('settings/payment-gateways', [PaymentGatewaySettingController::class, 'index'])
        ->name('settings.payment-gateways.index');
    Route::get('settings/payment-gateways/{id}', [PaymentGatewaySettingController::class, 'show'])
        ->name('settings.payment-gateways.show');
    Route::put('settings/payment-gateways/{id}', [PaymentGatewaySettingController::class, 'update'])
        ->name('settings.payment-gateways.update');

    Route::get('settings/pos', [PosSettingController::class, 'show'])
        ->name('settings.pos.show');
    Route::put('settings/pos', [PosSettingController::class, 'update'])
        ->name('settings.pos.update');

    Route::get('settings/reward-points', [RewardPointSettingController::class, 'show'])
        ->name('settings.reward-points.show');
    Route::put('settings/reward-points', [RewardPointSettingController::class, 'update'])
        ->name('settings.reward-points.update');

    Route::get('settings/hrm', [HrmSettingController::class, 'show'])
        ->name('settings.hrm.show');
    Route::put('settings/hrm', [HrmSettingController::class, 'update'])
        ->name('settings.hrm.update');

    Route::get('customers/all/active', [CustomerController::class, 'getAllActive'])
        ->name('customers.all-active');
    Route::delete('customers/bulk-destroy', [CustomerController::class, 'bulkDestroy'])
        ->name('customers.bulkDestroy');
    Route::post('customers/import', [CustomerController::class, 'import'])
        ->name('customers.import');
    Route::post('customers/export', [CustomerController::class, 'export'])
        ->name('customers.export');
    Route::get('customers/{customer}/summary', [CustomerController::class, 'summary'])
        ->name('customers.summary');
    Route::get('customers/{customer}/ledger', [CustomerController::class, 'ledger'])
        ->name('customers.ledger');
    Route::get('customers/{customer}/payments', [CustomerController::class, 'payments'])
        ->name('customers.payments');
    Route::get('customers/{customer}/deposits', [CustomerController::class, 'deposits'])
        ->name('customers.deposits.index');
    Route::post('customers/{customer}/deposits', [CustomerController::class, 'storeDeposit'])
        ->name('customers.deposits.store');
    Route::put('customers/{customer}/deposits/{deposit}', [CustomerController::class, 'updateDeposit'])
        ->name('customers.deposits.update');
    Route::delete('customers/{customer}/deposits/{deposit}', [CustomerController::class, 'destroyDeposit'])
        ->name('customers.deposits.destroy');
    Route::get('customers/{customer}/points', [CustomerController::class, 'points'])
        ->name('customers.points.index');
    Route::post('customers/{customer}/points', [CustomerController::class, 'storePoint'])
        ->name('customers.points.store');
    Route::put('customers/{customer}/points/{point}', [CustomerController::class, 'updatePoint'])
        ->name('customers.points.update');
    Route::delete('customers/{customer}/points/{point}', [CustomerController::class, 'destroyPoint'])
        ->name('customers.points.destroy');
    Route::apiResource('customers', CustomerController::class);

    Route::patch('customer-groups/bulk-activate', [CustomerGroupController::class, 'bulkActivate'])
        ->name('customer-groups.bulkActivate');
    Route::patch('customer-groups/bulk-deactivate', [CustomerGroupController::class, 'bulkDeactivate'])
        ->name('customer-groups.bulkDeactivate');
    Route::delete('customer-groups/bulk-destroy', [CustomerGroupController::class, 'bulkDestroy'])
        ->name('customer-groups.bulkDestroy');
    Route::post('customer-groups/import', [CustomerGroupController::class, 'import'])
        ->name('customer-groups.import');
    Route::post('customer-groups/export', [CustomerGroupController::class, 'export'])
        ->name('customer-groups.export');
    Route::get('customer-groups/all/active', [CustomerGroupController::class, 'getAllActive'])
        ->name('customer-groups.all-active');
    Route::apiResource('customer-groups', CustomerGroupController::class);

    Route::get('reports/customer-due', [ReportController::class, 'customerDueReport'])
        ->name('reports.customer-due');
    Route::post('reports/customer-due/export', [ReportController::class, 'exportCustomerDueReport'])
        ->name('reports.customer-due.export');

    Route::apiResource('currencies', CurrencyController::class);
    Route::delete('currencies/bulk-destroy', [CurrencyController::class, 'bulkDestroy'])
        ->name('currencies.bulkDestroy');

    Route::apiResource('couriers', CourierController::class);
    Route::delete('couriers/bulk-destroy', [CourierController::class, 'bulkDestroy'])
        ->name('couriers.bulkDestroy');

    Route::apiResource('variants', VariantController::class);
    Route::delete('variants/bulk-destroy', [VariantController::class, 'bulkDestroy'])
        ->name('variants.bulkDestroy');

    Route::delete('products/bulk-destroy', [ProductController::class, 'bulkDestroy'])
        ->name('products.bulkDestroy');
    Route::get('products/without-variant', [ProductController::class, 'getProductsWithoutVariant'])
        ->name('products.without-variant');
    Route::get('products/with-variant', [ProductController::class, 'getProductsWithVariant'])
        ->name('products.with-variant');
    Route::get('products/generate-code', [ProductController::class, 'generateCode'])
        ->name('products.generate-code');
    Route::post('products/import', [ProductController::class, 'import'])
        ->name('products.import');
    Route::post('products/{product}/reorder-images', [ProductController::class, 'reorderImages'])
        ->name('products.reorder-images');
    Route::get('products/search', [ProductController::class, 'search'])
        ->name('products.search');
    Route::get('products/sale-unit/{unitId}', [ProductController::class, 'getSaleUnits'])
        ->name('products.sale-unit');
    Route::get('products/combo-search', [ProductController::class, 'searchComboProduct'])
        ->name('products.combo-search');
    Route::get('products/{product}/history/sales', [ProductController::class, 'saleHistory'])
        ->name('products.history.sales');
    Route::get('products/{product}/history/purchases', [ProductController::class, 'purchaseHistory'])
        ->name('products.history.purchases');
    Route::get('products/{product}/history/sale-returns', [ProductController::class, 'saleReturnHistory'])
        ->name('products.history.sale-returns');
    Route::get('products/{product}/history/purchase-returns', [ProductController::class, 'purchaseReturnHistory'])
        ->name('products.history.purchase-returns');
    Route::get('products/{product}/history/adjustments', [ProductController::class, 'adjustmentHistory'])
        ->name('products.history.adjustments');
    Route::get('products/{product}/history/transfers', [ProductController::class, 'transferHistory'])
        ->name('products.history.transfers');
    Route::apiResource('products', ProductController::class);

    Route::get('suppliers/all/active', [SupplierController::class, 'getAllActive'])
        ->name('suppliers.all-active');
    Route::get('suppliers/{supplier}/ledger', [SupplierController::class, 'ledger'])
        ->name('suppliers.ledger');
    Route::get('suppliers/{supplier}/balance-due', [SupplierController::class, 'balanceDue'])
        ->name('suppliers.balance-due');
    Route::get('suppliers/{supplier}/payments', [SupplierController::class, 'payments'])
        ->name('suppliers.payments');
    Route::post('suppliers/{supplier}/clear-due', [SupplierController::class, 'clearDue'])
        ->name('suppliers.clear-due');
    Route::patch('suppliers/bulk-activate', [SupplierController::class, 'bulkActivate'])
        ->name('suppliers.bulkActivate');
    Route::patch('suppliers/bulk-deactivate', [SupplierController::class, 'bulkDeactivate'])
        ->name('suppliers.bulkDeactivate');
    Route::delete('suppliers/bulk-destroy', [SupplierController::class, 'bulkDestroy'])
        ->name('suppliers.bulkDestroy');
    Route::post('suppliers/import', [SupplierController::class, 'import'])
        ->name('suppliers.import');
    Route::post('suppliers/export', [SupplierController::class, 'export'])
        ->name('suppliers.export');
    Route::apiResource('suppliers', SupplierController::class);

    Route::apiResource('expense-categories', ExpenseCategoryController::class);
    Route::delete('expense-categories/bulk-destroy', [ExpenseCategoryController::class, 'bulkDestroy'])
        ->name('expense-categories.bulkDestroy');

    Route::apiResource('income-categories', IncomeCategoryController::class);
    Route::delete('income-categories/bulk-destroy', [IncomeCategoryController::class, 'bulkDestroy'])
        ->name('income-categories.bulkDestroy');
    Route::get('income-categories/generate-code', [IncomeCategoryController::class, 'generateCode'])
        ->name('income-categories.generate-code');

    Route::apiResource('incomes', IncomeController::class);
    Route::delete('incomes/bulk-destroy', [IncomeController::class, 'bulkDestroy'])
        ->name('incomes.bulkDestroy');

    Route::apiResource('departments', DepartmentController::class);

    Route::get('sale-agents/all/active', [SaleAgentController::class, 'getAllActive'])
        ->name('sale-agents.all-active');
    Route::delete('sale-agents/bulk-destroy', [SaleAgentController::class, 'bulkDestroy'])
        ->name('sale-agents.bulkDestroy');
    Route::apiResource('sale-agents', SaleAgentController::class)->parameters(['sale-agents' => 'sale_agent']);
    Route::delete('departments/bulk-destroy', [DepartmentController::class, 'bulkDestroy'])
        ->name('departments.bulkDestroy');

    Route::apiResource('designations', DesignationController::class);
    Route::delete('designations/bulk-destroy', [DesignationController::class, 'bulkDestroy'])
        ->name('designations.bulkDestroy');

    Route::apiResource('holidays', HolidayController::class);
    Route::delete('holidays/bulk-destroy', [HolidayController::class, 'bulkDestroy'])
        ->name('holidays.bulkDestroy');
    Route::post('holidays/{holiday}/approve', [HolidayController::class, 'approve'])
        ->name('holidays.approve');
    Route::get('holidays/user/{year}/{month}', [HolidayController::class, 'getUserHolidaysByMonth'])
        ->name('holidays.user-by-month');

    Route::apiResource('discount-plans', DiscountPlanController::class);
    Route::delete('discount-plans/bulk-destroy', [DiscountPlanController::class, 'bulkDestroy'])
        ->name('discount-plans.bulkDestroy');

    Route::apiResource('discounts', DiscountController::class);
    Route::delete('discounts/bulk-destroy', [DiscountController::class, 'bulkDestroy'])
        ->name('discounts.bulkDestroy');
    Route::get('discounts/product-search/{code}', [DiscountController::class, 'productSearch'])
        ->name('discounts.product-search');

    Route::apiResource('gift-cards', GiftCardController::class);
    Route::delete('gift-cards/bulk-destroy', [GiftCardController::class, 'bulkDestroy'])
        ->name('gift-cards.bulkDestroy');
    Route::get('gift-cards/generate-code', [GiftCardController::class, 'generateCode'])
        ->name('gift-cards.generate-code');
    Route::post('gift-cards/{gift_card}/recharge', [GiftCardController::class, 'recharge'])
        ->name('gift-cards.recharge');
});

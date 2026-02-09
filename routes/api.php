<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\SocialAuthController;
use App\Http\Controllers\Api\BrandController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourierController;
use App\Http\Controllers\Api\CurrencyController;
use App\Http\Controllers\Api\CustomerGroupController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\DesignationController;
use App\Http\Controllers\Api\DiscountController;
use App\Http\Controllers\Api\DiscountPlanController;
use App\Http\Controllers\Api\ExpenseCategoryController;
use App\Http\Controllers\Api\GiftCardController;
use App\Http\Controllers\Api\HolidayController;
use App\Http\Controllers\Api\IncomeCategoryController;
use App\Http\Controllers\Api\IncomeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\TaxController;
use App\Http\Controllers\Api\UnitController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VariantController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\AppSettingController;
use App\Http\Controllers\Api\GeneralSettingController;
use App\Http\Controllers\Api\MailSettingController;
use App\Http\Controllers\Api\CreateSmsController;
use App\Http\Controllers\Api\HrmSettingController;
use App\Http\Controllers\Api\PaymentGatewaySettingController;
use App\Http\Controllers\Api\PosSettingController;
use App\Http\Controllers\Api\RewardPointSettingController;
use App\Http\Controllers\Api\SmsSettingController;
use App\Http\Controllers\Api\WarehouseController;
use Illuminate\Http\Request;
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
    Route::get('users', [UserController::class, 'index'])->name('users.index');

    // All other API routes require authentication
    Route::get('categories/parents', [CategoryController::class, 'parents'])
        ->name('categories.parents');
    Route::patch('categories/bulk-activate', [CategoryController::class, 'bulkActivate'])
        ->name('categories.bulkActivate');
    Route::patch('categories/bulk-deactivate', [CategoryController::class, 'bulkDeactivate'])
        ->name('categories.bulkDeactivate');
    Route::patch('categories/bulk-enable-featured', [CategoryController::class, 'bulkEnableFeatured'])
        ->name('categories.bulkEnableFeatured');
    Route::patch('categories/bulk-disable-featured', [CategoryController::class, 'bulkDisableFeatured'])
        ->name('categories.bulkDisableFeatured');
    Route::patch('categories/bulk-enable-sync', [CategoryController::class, 'bulkEnableSync'])
        ->name('categories.bulkEnableSync');
    Route::patch('categories/bulk-disable-sync', [CategoryController::class, 'bulkDisableSync'])
        ->name('categories.bulkDisableSync');
    Route::delete('categories/bulk-destroy', [CategoryController::class, 'bulkDestroy'])
        ->name('categories.bulkDestroy');
    Route::post('categories/import', [CategoryController::class, 'import'])
        ->name('categories.import');
    Route::post('categories/export', [CategoryController::class, 'export'])
        ->name('categories.export');
    Route::apiResource('categories', CategoryController::class);

    Route::patch('brands/bulk-activate', [BrandController::class, 'bulkActivate'])
        ->name('brands.bulkActivate');
    Route::patch('brands/bulk-deactivate', [BrandController::class, 'bulkDeactivate'])
        ->name('brands.bulkDeactivate');
    Route::delete('brands/bulk-destroy', [BrandController::class, 'bulkDestroy'])
        ->name('brands.bulkDestroy');
    Route::post('brands/import', [BrandController::class, 'import'])
        ->name('brands.import');
    Route::post('brands/export', [BrandController::class, 'export'])
        ->name('brands.export');
    Route::apiResource('brands', BrandController::class);

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

    Route::get('activity-logs', [ActivityLogController::class, 'index'])
        ->name('activity-logs.index');

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

    Route::apiResource('customer-groups', CustomerGroupController::class);
    Route::delete('customer-groups/bulk-destroy', [CustomerGroupController::class, 'bulkDestroy'])
        ->name('customer-groups.bulkDestroy');
    Route::post('customer-groups/import', [CustomerGroupController::class, 'import'])
        ->name('customer-groups.import');
    Route::get('customer-groups/all/active', [CustomerGroupController::class, 'getAllActive'])
        ->name('customer-groups.all-active');

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
<?php

use Illuminate\Support\Facades\Route;
use Modules\Woocommerce\Http\Controllers\WoocommerceController;

Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
    Route::apiResource('woocommerces', WoocommerceController::class)->names('woocommerce');
});

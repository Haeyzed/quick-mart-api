<?php

use Illuminate\Support\Facades\Route;
use Modules\Woocommerce\Http\Controllers\WoocommerceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('woocommerces', WoocommerceController::class)->names('woocommerce');
});

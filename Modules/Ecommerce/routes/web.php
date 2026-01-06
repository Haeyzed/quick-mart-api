<?php

use Illuminate\Support\Facades\Route;
use Modules\Ecommerce\Http\Controllers\EcommerceController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('ecommerces', EcommerceController::class)->names('ecommerce');
});

<?php

use Illuminate\Support\Facades\Route;
use Modules\Manufacturing\Http\Controllers\ManufacturingController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('manufacturings', ManufacturingController::class)->names('manufacturing');
});

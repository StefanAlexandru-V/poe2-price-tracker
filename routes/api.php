<?php

use App\Http\Controllers\Api\PriceController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/prices', [PriceController::class, 'index']);
    Route::get('/prices/{ninjaId}', [PriceController::class, 'show']);
});

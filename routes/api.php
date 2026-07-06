<?php

use App\Http\Controllers\Api\PriceController;
use App\Http\Middleware\VerifyApiKey;
use Illuminate\Support\Facades\Route;

// Public, rate limited
Route::prefix('v1')->middleware('throttle:60,1')->group(function () {
    Route::get('/prices', [PriceController::class, 'index']);
    Route::get('/prices/{ninjaId}', [PriceController::class, 'show']);
    Route::get('/prices/{ninjaId}/history', [PriceController::class, 'aggregated']);
});

// Authenticated (higher rate limit)
Route::prefix('v1')->middleware(['throttle:200,1', VerifyApiKey::class])->group(function () {
    // placeholder for future write endpoints (webhooks, bulk export, etc.)
});

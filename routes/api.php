<?php

use App\Http\Controllers\Api\AccountApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\PaymentAgentController;
use Illuminate\Support\Facades\Route;

// Public signal webhook for phone forwarders / Gmail automation (with secret token check)
Route::prefix('v1')->group(function () {
    Route::post('agent/webhook-signal', [PaymentAgentController::class, 'ingest'])
        ->middleware('throttle:60,1');
});

// Authenticated API routes with API Key
Route::prefix('v1')->middleware(['api.key'])->group(function () {
    Route::get('accounts', [AccountApiController::class, 'index']);
    Route::post('orders', [OrderApiController::class, 'store']);
    Route::get('orders/{orderNo}', [OrderApiController::class, 'show']);

    // Payment Verification Agent endpoints
    Route::post('agent/signal', [PaymentAgentController::class, 'ingest']);
    Route::get('agent/signals', [PaymentAgentController::class, 'listSignals']);
    Route::post('agent/verify-order/{orderNo}', [PaymentAgentController::class, 'verifyOrder']);
});

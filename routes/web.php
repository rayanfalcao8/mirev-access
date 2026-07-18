<?php

use App\Http\Controllers\ClientPortalController;
use App\Http\Controllers\PaymentController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/connect/demo');

Route::get('/connect/{site:slug}', [ClientPortalController::class, 'show'])
    ->name('client.portal');

Route::post('/connect/{site:slug}/purchase', [ClientPortalController::class, 'purchase'])
    ->name('client.purchase');

Route::get('/payments/{attempt}', [PaymentController::class, 'show'])
    ->name('payments.show');

Route::post('/payments/{attempt}/simulate-success', [PaymentController::class, 'simulateSuccess'])
    ->name('payments.simulate-success');

Route::get('/connect/{site:slug}/subscriptions/{subscription}', [ClientPortalController::class, 'success'])
    ->name('client.success');

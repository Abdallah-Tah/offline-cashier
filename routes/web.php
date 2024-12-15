<?php

use Illuminate\Support\Facades\Route;
use AMohamed\OfflineCashier\Http\Controllers\SubscriptionController;
use AMohamed\OfflineCashier\Http\Controllers\WebhookController;

Route::middleware(['web', 'auth'])->group(function () {
    Route::prefix('subscriptions')->name('subscriptions.')->group(function () {
        Route::get('/', [SubscriptionController::class, 'index'])->name('index');
        Route::get('/plans', [SubscriptionController::class, 'plans'])->name('plans');
        Route::post('/subscribe/{plan}', [SubscriptionController::class, 'subscribe'])->name('subscribe');
        Route::post('/cancel', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/resume', [SubscriptionController::class, 'resume'])->name('resume');
        Route::get('/invoices', [SubscriptionController::class, 'invoices'])->name('invoices');
        Route::get('/invoices/{invoice}/download', [SubscriptionController::class, 'downloadInvoice'])->name('invoices.download');
    });
});

// Stripe webhook route
Route::post(
    'stripe/webhook',
    [WebhookController::class, 'handleWebhook']
)->name('offline-cashier.webhook')
->middleware('api'); 
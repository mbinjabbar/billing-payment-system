<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;
use App\Http\Controllers\documentController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\procedurecodesController;
use App\Http\Controllers\insurancefirmsController;
use App\Http\Controllers\SettingsController;
use App\Http\Rules\StoreBillRules;
use App\Http\Rules\UpdateBillRules;
use App\Http\Rules\StorePaymentRules;
use App\Http\Rules\UpdatePaymentRules;

// Unauthenticated Routes
Route::get('/bills/invoice/{id}',           [documentController::class, 'downloadInvoice']);
Route::get('/bills/nf2/{id}',               [documentController::class, 'downloadNF2']);
Route::get('/payments/receipt/{paymentId}', [documentController::class, 'downloadReceipt']);
Route::get('/documents/cheque/{id}',        [documentController::class, 'downloadCheque']);
Route::get('/settings',                     [SettingsController::class, 'index']);

Route::middleware('firebasejwt')->group(function () {

    // All Roles — read only
    Route::get('/bills/stats', [billController::class, 'stats']);
    Route::get('/bills',       [billController::class, 'index']);
    Route::get('/bills/{id}',  [billController::class, 'show']);
    Route::get('/documents',   [documentController::class, 'index']);

    // Admin + Biller
    Route::middleware('role:Admin,Biller')->group(function () {

        // Visits
        Route::apiResource('visits', VisitController::class)->only(['index', 'show']);

        // Bills
        Route::post('/bills', [billController::class, 'store'])
            ->middleware('validate:' . StoreBillRules::class);

        Route::put('/bills/{id}', [billController::class, 'update'])
            ->middleware('validate:' . UpdateBillRules::class);

        Route::post('/bills/export',       [billController::class, 'export']);
        Route::patch('/bills/{id}/status', [billController::class, 'updateStatus']);

        Route::get('/procedurecodes', [procedurecodesController::class, 'index']);
        Route::get('/insurancefirms', [insurancefirmsController::class, 'index']);
    });

    // Admin + Payment Poster
    Route::middleware('role:Admin,Payment Poster')->group(function () {

        Route::get('/payments',      [paymentController::class, 'index']);
        Route::get('/payments/{id}', [paymentController::class, 'show']);

        // filevalidation + body validation
        Route::post('/payments', [paymentController::class, 'store'])
            ->middleware(['filevalidation', 'validate:' . StorePaymentRules::class]);

        Route::post('/payments/{id}', [paymentController::class, 'update'])
            ->middleware(['filevalidation', 'validate:' . UpdatePaymentRules::class]);

        Route::post('/payments/export',       [paymentController::class, 'export']);
        Route::patch('/payments/{id}/refund', [paymentController::class, 'refund']);
    });

    // Admin Only
    Route::middleware('role:Admin')->group(function () {

        Route::delete('/bills/{id}',    [billController::class, 'destroy']);
        Route::delete('/payments/{id}', [paymentController::class, 'destroy']);

        // Procedure codes + Insurance firms
        Route::apiResource('/procedurecodes', procedurecodesController::class)->except(['index']);
        Route::apiResource('/insurancefirms', insurancefirmsController::class)->except(['index']);

        // Settings — no validation needed (key-value pairs)
        Route::post('/settings', [SettingsController::class, 'update']);
    });
});
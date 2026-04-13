<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;
use App\Http\Controllers\documentController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\procedurecodesController;
use App\Http\Controllers\insurancefirmsController;
use App\Http\Controllers\SettingsController;

Route::middleware('firebasejwt')->group(function () {

    // All roles — bills read + invoice download + stats + documents listing
    Route::get('/bills/stats',        [billController::class, 'stats']);
    Route::get('/bills/invoice/{id}', [documentController::class, 'downloadInvoice']);
    Route::get('/bills',              [billController::class, 'index']);
    Route::get('/bills/{id}',         [billController::class, 'show']);
    Route::get('/documents',          [documentController::class, 'index']);

    // Admin + Biller
    Route::middleware('role:Admin,Biller')->group(function () {
        Route::apiResource('visits', VisitController::class)->only(['index', 'show']);
        Route::post('/bills',              [billController::class, 'store']);
        Route::put('/bills/{id}',          [billController::class, 'update']);
        Route::post('/bills/export',       [billController::class, 'export']);
        Route::patch('/bills/{id}/status', [billController::class, 'updateStatus']);
        Route::get('/bills/nf2/{id}',      [documentController::class, 'downloadNF2']);
        Route::get('/procedurecodes',      [procedurecodesController::class, 'index']);
        Route::get('/insurancefirms',      [insurancefirmsController::class, 'index']);
    });

    // Admin + Payment Poster
    Route::middleware('role:Admin,Payment Poster')->group(function () {
        Route::apiResource('/payments', paymentController::class)->except(['destroy']);
        Route::post('/payments/export',             [paymentController::class, 'export']);
        Route::get('/payments/receipt/{paymentId}', [documentController::class, 'downloadReceipt']);
        Route::get('/documents/cheque/{id}',        [documentController::class, 'downloadCheque']);
    });

    // Admin only
    Route::middleware('role:Admin')->group(function () {
        Route::delete('/bills/{id}',    [billController::class, 'destroy']);
        Route::delete('/payments/{id}', [paymentController::class, 'destroy']);
        Route::apiResource('/procedurecodes', procedurecodesController::class)->except(['index']);
        Route::apiResource('/insurancefirms', insurancefirmsController::class)->except(['index']);
        Route::get('/settings',  [SettingsController::class, 'index']);
        Route::post('/settings', [SettingsController::class, 'update']);
    });

});
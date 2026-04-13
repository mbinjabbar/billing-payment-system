<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;
use App\Http\Controllers\documentController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\procedurecodesController;
use App\Http\Controllers\insurancefirmsController;
use App\Http\Controllers\SettingsController;



Route::group(['middleware' => ['firebasejwt']], function () {

Route::apiResource('visits', VisitController::class)->only(['index', 'show'])->middleware('role:Admin|Biller');

Route::get('/bills/stats', [billController::class, 'stats']);
Route::post('/bills/export', [billController::class, 'export']);
Route::patch('/bills/{id}/status', [billController::class, 'updateStatus']);
Route::get('/documents/cheque/{id}', [documentController::class, 'downloadCheque']);
Route::get('/bills/invoice/{id}', [documentController::class, 'downloadInvoice']);
Route::get('/bills/nf2/{id}', [documentController::class, 'downloadNF2']);

Route::get('/bills', [billController::class,'index']);
Route::get('/bills/{id}', [billController::class,'show'])->middleware('role:Admin|Biller|Payment Poster');

Route::apiResource('/bills', billController::class)->only(['store','update'])->middleware('role:Admin|Biller');

Route::middleware(['role:Admin|Payment Poster'])->group(function () {
    Route::get('/payments/receipt/{paymentId}', [documentController::class, 'downloadReceipt']);
    Route::post('/payments/export', [paymentController::class, 'export']);
});

Route::middleware('role:Admin|Payment Poster')->group(function () {
    Route::apiResource('payments', PaymentController::class)->only([
       'store' ,'index', 'show', 'update'
    ]);
});


Route::get('/procedurecodes', [procedurecodesController::class,'index'])->middleware('role:Admin|Biller');
Route::get('/insurancefirms', [insurancefirmsController::class,'index'])->middleware('role:Admin|Biller');
Route::apiResource('/documents', documentController::class);

Route::middleware(['role:Admin'])->group(function () {
    Route::delete('/bills/{id}', [billController::class, 'destroy']);
    Route::delete('/payments/{id}', [PaymentController::class, 'destroy']);
    Route::apiResource('/procedurecodes', procedurecodesController::class)->only(['show','update','destroy']);
    Route::apiResource('/insurancefirms', insurancefirmsController::class)->only(['show','update','destroy']);
    Route::get('/settings', [SettingsController::class, 'index']);
    Route::post('/settings', [SettingsController::class, 'update']);
});
});






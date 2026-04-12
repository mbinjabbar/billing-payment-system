<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;
use App\Http\Controllers\documentController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\procedurecodesController;
use App\Http\Controllers\insurancefirmsController;
use App\Http\Controllers\SettingsController;


Route::get('/bills/stats', [billController::class, 'stats']);
Route::post('/bills/export', [billController::class, 'export']);
Route::get('/bills/pdf/{id}', [billController::class, 'downloadPDF']);
Route::apiResource('/bills', billController::class);

Route::post('/payments/export', [paymentController::class, 'export']);
Route::apiResource('/payments', paymentController::class);

Route::apiResource('visits', VisitController::class)->only(['index', 'show']);
Route::apiResource('/procedurecodes', procedurecodesController::class);
Route::apiResource('/insurancefirms', insurancefirmsController::class);
Route::apiResource('/documents', documentController::class);

Route::get('/settings', [SettingsController::class, 'index']);
Route::post('/settings', [SettingsController::class, 'update']);
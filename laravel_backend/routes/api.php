<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;
use App\Http\Controllers\documentController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\procedurecodesController;
use App\Http\Controllers\insurancefirmsController;


Route::apiResource('visits', VisitController::class)->only(['index', 'show']);
Route::apiResource('/bills', billController::class);
Route::get('/bills/pdf/{id}', [billController::class, 'downloadPDF']);
Route::apiResource('/payments', paymentController::class);
Route::apiResource('/procedurecodes', procedurecodesController::class);
Route::apiResource('/insurancefirms', insurancefirmsController::class);
Route::apiResource('/documents', documentController::class);


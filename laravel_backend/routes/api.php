<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;
use App\Http\Controllers\paymentController;
use App\Http\Controllers\procedurecodesController;
use App\Http\Controllers\insurancefirmsController;


Route::apiResource('visits', VisitController::class)->only(['index', 'show']);
Route::apiResource('/bills', billController::class);

Route::get('/bills/pdf/{id}', [billController::class, 'generatePDF']);
Route::apiResource('/payments', paymentController::class);
Route::get('/procedurecodes', [procedurecodesController::class, 'index']);
Route::get('/insurancefirms', [insurancefirmsController::class, 'index']);


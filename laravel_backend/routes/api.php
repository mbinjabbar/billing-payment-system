<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;


Route::apiResource('visits', VisitController::class)->only(['index', 'show']);
Route::apiResource('/bills', billController::class);

Route::get('/bills/pdf/{id}', [billController::class, 'generatePDF']);
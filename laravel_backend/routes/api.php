<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\billController;


Route::get('/visits', [VisitController::class, 'index']);
Route::get('/visits/{id}', [VisitController::class, 'getVisitById']);
Route::post('/generate-bill', [billController::class, 'generateBill']);

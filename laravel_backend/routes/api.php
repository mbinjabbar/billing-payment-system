<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\VisitController;


Route::get('/visits', [VisitController::class, 'index']);
Route::get('/visits/{visitId}', [VisitController::class, 'getVisitDetails']);

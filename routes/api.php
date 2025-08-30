<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DependentDropdownController;
use App\Http\Controllers\QRController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/regencies', [DependentDropdownController::class, 'getRegencies']);
Route::get('/districts', [DependentDropdownController::class, 'getDistricts']);


<?php

use App\Http\Controllers\Api\DispenseController;
use App\Http\Controllers\Auth\SecurityPinController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::post('/dispense', [DispenseController::class, 'dispense']);

Route::post('/validate-pin', [SecurityPinController::class, 'validatePin']);

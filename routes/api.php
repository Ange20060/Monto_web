<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\VehicleController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/driver/profile', [DriverController::class, 'profile']);

    Route::put('/driver/profile', [DriverController::class, 'update']);

    Route::post('/driver/online', [DriverController::class, 'goOnlien']);

    Route::post('/driver/offline', [DriverController::class, 'goOffline']);

    Route::get('/driver/status', [DriverController::class, 'status']);

    Route::post('/driver/vehicle', [VehicleController::class, 'store']);

    Route::post('/driver/trip', [TripController::class,'store']);

});

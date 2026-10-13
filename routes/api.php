<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\DriverController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\RatingController;
use App\Http\Controllers\Api\TripController;
use App\Http\Controllers\Api\VehicleController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/driver/profile', [DriverController::class, 'profile']);
    Route::post('/driver/profile', [DriverController::class, 'store']);
    Route::put('/driver/profile', [DriverController::class, 'update']);
    Route::post('/driver/online', [DriverController::class, 'goOnlien']);
    Route::post('/driver/offline', [DriverController::class, 'goOffline']);
    Route::get('/driver/status', [DriverController::class, 'status']);

    Route::get('/driver/vehicles', [VehicleController::class, 'index']);
    Route::post('/driver/vehicle', [VehicleController::class, 'store']);
    Route::put('/driver/vehicle/{id}', [VehicleController::class, 'update']);
    Route::delete('/driver/vehicle/{id}', [VehicleController::class, 'destroy']);

    Route::get('/driver/trips', [TripController::class, 'driverTrips']);
    Route::post('/driver/trip', [TripController::class, 'store']);

    Route::get('/trips', [TripController::class, 'index']);
    Route::get('/trips/{id}', [TripController::class, 'show']);
    Route::put('/trips/{id}', [TripController::class, 'update']);
    Route::delete('/trips/{id}', [TripController::class, 'destroy']);

    Route::get('/bookings', [BookingController::class, 'index']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/bookings/{id}', [BookingController::class, 'show']);
    Route::put('/bookings/{id}', [BookingController::class, 'update']);
    Route::delete('/bookings/{id}', [BookingController::class, 'destroy']);

    Route::get('/payments/history', [PaymentController::class, 'history']);
    Route::post('/payments', [PaymentController::class, 'store']);

    Route::post('/ratings', [RatingController::class, 'store']);
    Route::get('/ratings/driver/{driverId}', [RatingController::class, 'driverRatings']);
});

<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CabanaController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\AvailabilityController;
use App\Http\Controllers\Api\AdminCabanaController;
use App\Http\Controllers\Api\AdminBookingController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminPaymentController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class , 'register']);
    Route::post('/login', [AuthController::class , 'login']);
    Route::post('/logout', [AuthController::class , 'logout'])->middleware('auth:sanctum');

    // Public Cabana Routes
    Route::get('/cabanas', [CabanaController::class , 'index']);
    Route::get('/cabanas/{id}', [CabanaController::class , 'show']);
    Route::get('/cabanas/{id}/reviews', [ReviewController::class , 'index']);

    // Availability
    Route::post('/cabanas/{id}/check-availability', [AvailabilityController::class , 'check']);
    Route::get('/cabanas/{id}/calendar', [AvailabilityController::class , 'calendar']);

    // Protected Customer Routes
    Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', [AuthController::class , 'user']);
            Route::post('/bookings', [BookingController::class , 'store']);
            Route::get('/bookings', [BookingController::class , 'index']);
            Route::get('/bookings/{id}', [BookingController::class , 'show']);

            Route::post('/payments/initiate', [PaymentController::class , 'initiate']);
            Route::get('/payments/my-payments', [PaymentController::class , 'myPayments']);
            Route::post('/bookings/{id}/reviews', [ReviewController::class , 'store']);
        }
        );

        // Public Webhook for Payment
        Route::post('/payments/payhere-webhook', [PaymentController::class , 'webhook']);

        // Protected Admin Routes
        Route::middleware(['auth:sanctum', \App\Http\Middleware\AdminMiddleware::class])->prefix('admin')->group(function () {
            Route::apiResource('cabanas', AdminCabanaController::class);
            Route::post('/cabanas/{id}/images', [AdminCabanaController::class , 'uploadImage']);
            Route::delete('/images/{id}', [AdminCabanaController::class , 'deleteImage']);
            Route::post('/cabanas/{id}/amenities', [AdminCabanaController::class , 'syncAmenities']);

            // Admin availability overrides
            Route::post('/cabanas/{id}/block-dates', [\App\Http\Controllers\Api\AdminAvailabilityController::class , 'blockDates']);
            Route::delete('/availability/{id}', [\App\Http\Controllers\Api\AdminAvailabilityController::class , 'removeBlock']);

            Route::get('/bookings', [AdminBookingController::class , 'index']);
            Route::get('/payments', [AdminPaymentController::class , 'index']);
            Route::get('/dashboard/stats', [AdminDashboardController::class , 'stats']);
        }
        );
    });

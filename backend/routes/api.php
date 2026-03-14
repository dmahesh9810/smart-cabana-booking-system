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
use App\Http\Controllers\Api\RecommendationController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::post('/register', [AuthController::class , 'register']);
    Route::post('/login', [AuthController::class , 'login']);
    Route::post('/logout', [AuthController::class , 'logout'])->middleware('auth:sanctum');

    // Public Cabana Routes
    Route::get('/cabanas', [CabanaController::class , 'index']);
    Route::get('/cabanas/{id}', [CabanaController::class , 'show']);
    Route::get('/cabanas/{id}/reviews', [ReviewController::class , 'index']);
    Route::get('/recommendations', [RecommendationController::class, 'index']);


    // Availability
    Route::post('/cabanas/{id}/check-availability', [AvailabilityController::class , 'check']);
    Route::get('/cabanas/{id}/calendar', [AvailabilityController::class , 'calendar']);
    Route::get('/cabanas/{id}/availability', [AvailabilityController::class , 'availability']);


    // Protected Customer Routes
    Route::middleware('auth:sanctum')->group(function () {
            Route::get('/user', [AuthController::class , 'user']);
            Route::post('/bookings', [BookingController::class , 'store']);
            Route::get('/bookings', [BookingController::class , 'index']);
            Route::get('/bookings/{id}', [BookingController::class , 'show']);

            // Personalized Recommendations (requires auth to identify user)
            Route::get('/recommendations/personalized', [RecommendationController::class, 'personalized']);

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
            Route::patch('/cabanas/{id}/status', [AdminCabanaController::class, 'toggleStatus']);
            Route::post('/cabanas/{id}/images', [AdminCabanaController::class , 'uploadImage']);
            Route::delete('/images/{id}', [AdminCabanaController::class , 'deleteImage']);
            Route::post('/cabanas/{id}/amenities', [AdminCabanaController::class , 'syncAmenities']);

            // Admin availability overrides
            Route::post('/cabanas/{id}/block-dates', [\App\Http\Controllers\Api\AdminAvailabilityController::class , 'blockDates']);
            Route::delete('/availability/{id}', [\App\Http\Controllers\Api\AdminAvailabilityController::class , 'removeBlock']);

            // Admin Booking Routes
            Route::get('/bookings', [AdminBookingController::class, 'index']);
            Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
            Route::patch('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
            Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy']);

            Route::get('/payments', [AdminPaymentController::class , 'index']);
            Route::get('/dashboard', [AdminDashboardController::class , 'stats']);
            Route::get('/dashboard/stats', [AdminDashboardController::class , 'stats']);

        }
        );
    });

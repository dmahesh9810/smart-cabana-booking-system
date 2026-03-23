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
use App\Http\Controllers\Api\VerificationController;
use App\Http\Controllers\Api\NotificationController;

Route::prefix('v1')->group(function () {
    // Auth Routes
    Route::middleware('throttle:10,1')->group(function () {
        Route::post('/register', [AuthController::class , 'register']);
        Route::post('/login', [AuthController::class , 'login']);
    });
    
    Route::post('/logout', [AuthController::class , 'logout'])->middleware(['auth:sanctum', 'throttle:10,1']);

    // Email Verification Routes
    Route::get('/email/verify/{id}/{hash}', [VerificationController::class, 'verify'])
        ->name('verification.verify')
        ->middleware(['throttle:6,1']);

    Route::post('/email/resend', [VerificationController::class, 'resend'])
        ->middleware(['auth:sanctum', 'throttle:6,1'])
        ->name('verification.resend');

    // Public Read Routes
    Route::middleware('throttle:60,1')->group(function () {
        Route::get('/cabanas', [CabanaController::class , 'index']);
        Route::get('/cabanas/{id}', [CabanaController::class , 'show']);
        Route::get('/cabanas/{id}/reviews', [ReviewController::class , 'index']);
        Route::get('/recommendations', [RecommendationController::class, 'index']);

        // Availability
        Route::post('/cabanas/{id}/check-availability', [AvailabilityController::class , 'check']);
        Route::get('/cabanas/{id}/calendar', [AvailabilityController::class , 'calendar']);
        Route::get('/cabanas/{id}/availability', [AvailabilityController::class , 'availability']);
    });


    // Protected Customer Routes
    Route::middleware(['auth:sanctum', 'throttle:30,1'])->group(function () {
            Route::get('/user', [AuthController::class , 'user']);
            Route::post('/bookings', [BookingController::class , 'store']);
            Route::get('/bookings', [BookingController::class , 'index']);
            Route::get('/bookings/{id}', [BookingController::class , 'show']);

            // Personalized Recommendations (requires auth to identify user)
            Route::get('/recommendations/personalized', [RecommendationController::class, 'personalized']);

            Route::post('/payments/initiate', [PaymentController::class , 'initiate']);

            Route::get('/payments/my-payments', [PaymentController::class , 'myPayments']);
            Route::post('/bookings/{id}/reviews', [ReviewController::class , 'store']);

            // Notifications
            Route::get('/notifications', [NotificationController::class, 'index']);
            Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
            // Standardizing trailing slash but let's stick to simple ones
            Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        }
    );

    // Public Webhook for Payment
    Route::post('/payments/payhere-webhook', [PaymentController::class , 'webhook']);

    // Channel Manager Webhook
    Route::post('/bookingcom/webhook', [\App\Http\Controllers\Api\WebhookController::class, 'handleBookingComWebhook']);

    // Protected Management Routes (Admin & Staff)
    Route::middleware(['auth:sanctum', 'role:admin,staff', 'throttle:30,1'])->prefix('admin')->group(function () {
        // Cabanas
        Route::get('/cabanas', [AdminCabanaController::class, 'index']);
        Route::get('/cabanas/{id}', [AdminCabanaController::class, 'show']);
        Route::post('/cabanas', [AdminCabanaController::class, 'store']);
        Route::put('/cabanas/{id}', [AdminCabanaController::class, 'update']);
        Route::delete('/cabanas/{id}', [AdminCabanaController::class, 'destroy'])->middleware('role:admin');

        Route::patch('/cabanas/{id}/status', [AdminCabanaController::class, 'toggleStatus']);
        Route::post('/cabanas/{id}/images', [AdminCabanaController::class , 'uploadImage']);
        Route::delete('/images/{id}', [AdminCabanaController::class , 'deleteImage'])->middleware('role:admin');
        Route::post('/cabanas/{id}/amenities', [AdminCabanaController::class , 'syncAmenities']);

        // Admin availability overrides
        Route::post('/cabanas/{id}/block-dates', [\App\Http\Controllers\Api\AdminAvailabilityController::class , 'blockDates']);
        Route::delete('/availability/{id}', [\App\Http\Controllers\Api\AdminAvailabilityController::class , 'removeBlock']);

        // Admin Booking Routes
        Route::get('/bookings', [AdminBookingController::class, 'index']);
        Route::get('/bookings/{id}', [AdminBookingController::class, 'show']);
        Route::patch('/bookings/{id}/status', [AdminBookingController::class, 'updateStatus']);
        Route::delete('/bookings/{id}', [AdminBookingController::class, 'destroy'])->middleware('role:admin');

        Route::get('/payments', [AdminPaymentController::class , 'index']);
        Route::get('/dashboard', [AdminDashboardController::class , 'stats']);
        Route::get('/dashboard/stats', [AdminDashboardController::class , 'stats']);

        // Admin Report Routes (Admin & Staff)
        Route::prefix('reports')->group(function () {
            Route::get('/bookings', [\App\Http\Controllers\Api\AdminReportController::class, 'bookings']);
            Route::get('/revenue', [\App\Http\Controllers\Api\AdminReportController::class, 'revenue']);
            Route::get('/occupancy', [\App\Http\Controllers\Api\AdminReportController::class, 'occupancy']);
            Route::get('/export/bookings', [\App\Http\Controllers\Api\AdminReportController::class, 'exportBookings']);
            Route::get('/export/revenue', [\App\Http\Controllers\Api\AdminReportController::class, 'exportRevenue']);
            Route::get('/export/occupancy', [\App\Http\Controllers\Api\AdminReportController::class, 'exportOccupancy']);
        });
    });
});

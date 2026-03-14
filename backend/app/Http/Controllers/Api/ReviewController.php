<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Cabana;
use App\Services\ReviewService;
use App\Traits\ApiResponse;

class ReviewController extends Controller
{
    use ApiResponse;

    private ReviewService $reviewService;

    public function __construct(ReviewService $reviewService)
    {
        $this->reviewService = $reviewService;
    }

    /**
     * Display a listing of the reviews for a cabana.
     */
    public function index($cabanaId)
    {
        $cabana = Cabana::findOrFail($cabanaId);
        // Eager load user to avoid N+1 when formatting reviews
        $reviews = $cabana->reviews()->with('user')->latest()->get();

        return $this->successResponse(ReviewResource::collection($reviews), 'Reviews retrieved successfully');
    }

    /**
     * Store a newly created review for a booking.
     */
    public function store(CreateReviewRequest $request, $bookingId)
    {
        $user = $request->user();

        $booking = $this->reviewService->validateBookingEligibility($bookingId, $user->id);

        $review = $this->reviewService->createReview($booking, $request->validated());

        return $this->successResponse(new ReviewResource($review->load('user')), 'Review submitted successfully', 201);
    }
}

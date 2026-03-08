<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreReviewRequest;
use App\Http\Resources\ReviewResource;
use App\Models\Cabana;
use App\Services\ReviewService;

class ReviewController extends Controller
{
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

        return ReviewResource::collection($reviews);
    }

    /**
     * Store a newly created review for a booking.
     */
    public function store(StoreReviewRequest $request, $bookingId)
    {
        $user = $request->user();

        $booking = $this->reviewService->validateBookingEligibility($bookingId, $user->id);

        $review = $this->reviewService->createReview($booking, $request->validated());

        return response()->json([
            'message' => 'Review submitted successfully',
            'data' => new ReviewResource($review->load('user'))
        ], 201);
    }
}

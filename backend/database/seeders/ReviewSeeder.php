<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\Booking;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get completed bookings
        $bookings = Booking::where('status', 'completed')->get();

        if ($bookings->isEmpty()) {
            return;
        }

        $comments = [
            "Absolutely breath-taking! Highly recommend.",
            "The cabana was spotless and the staff was very friendly.",
            "Incredible view, we will definitely be coming back.",
            "Loved the pool access! Perfect stay.",
            "Very relaxing environment. A true escape.",
            "5 stars all the way! Amazing experience.",
            "The sunset from the porch was magical.",
            "Great value for money. Loved the breakfast.",
            "Best vacation ever! The cabana was perfect.",
            "Very private and peaceful. Exactly what we needed.",
            "Excellent service and beautiful surroundings."
        ];

        foreach ($bookings as $booking) {
            // Random rating between 3 and 5
            $rating = rand(3, 5);
            $comment = $comments[array_rand($comments)];

            Review::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'user_id' => $booking->user_id,
                    'cabana_id' => $booking->cabana_id,
                    'rating' => $rating,
                    'comment' => $comment,
                    'created_at' => $booking->updated_at, // Aligned with when stay completed
                    'updated_at' => $booking->updated_at,
                ]
            );
        }
    }
}

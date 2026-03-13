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
        // Get up to 15 confirmed bookings
        $bookings = Booking::where('status', 'confirmed')->inRandomOrder()->limit(15)->get();

        $comments = [
            "Absolutely breath-taking! Highly recommend.",
            "The cabana was spotless and the staff was very friendly.",
            "Not bad for the price, but could be cleaner.",
            "Incredible view, we will definitely be coming back.",
            "It was okay, but the location was a bit noisy at night.",
            "Loved the pool access! Perfect stay.",
            "Very relaxing environment. A true escape.",
            "A bit overpriced, but overall a decent stay.",
            "5 stars all the way! Amazing experience.",
            "Could use some maintenance, but fine for a weekend trip."
        ];

        foreach ($bookings as $booking) {
            // Random rating between 3 and 5, biased towards good to make 'popular' feature shine
            $rating = rand(3, 5);
            $comment = $comments[array_rand($comments)];

            Review::updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'user_id' => $booking->user_id,
                    'cabana_id' => $booking->cabana_id,
                    'rating' => $rating,
                    'comment' => $comment,
                ]
            );
        }
    }
}

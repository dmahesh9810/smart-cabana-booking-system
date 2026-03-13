<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Cabana;

class RecommendationController extends Controller
{
    /**
     * Get the top 3 recommended cabanas based on confirmed booking counts.
     */
    public function index(): JsonResponse
    {
        // Get the top 3 most booked cabana IDs where the booking was confirmed
        $popularCabanaIds = DB::table('bookings')
            ->select('cabana_id', DB::raw('COUNT(*) as total_bookings'))
            ->where('status', 'confirmed')
            ->groupBy('cabana_id')
            ->orderByDesc('total_bookings')
            ->limit(3)
            ->pluck('cabana_id');

        // Check if we actually have popular ones
        if ($popularCabanaIds->isEmpty()) {
            // Fallback: Just return 3 active cabanas if no bookings exist yet
            $cabanas = Cabana::where('is_active', true)
                ->with('images')
                ->inRandomOrder()
                ->take(3)
                ->get();
        } else {
            // Retrieve full Eloquent models for the top ID targets, ensuring they are active
            // Order them according to the $popularCabanaIds array order using FIND_IN_SET
            $idsSql = $popularCabanaIds->implode(',');
            
            $cabanas = Cabana::whereIn('id', $popularCabanaIds)
                ->where('is_active', true)
                ->with('images')
                ->orderByRaw("FIND_IN_SET(id, '{$idsSql}')")
                ->get();
        }

        // Format the UI structure and ensure the image is a full URL safely via asset() wrapper
        $formatted = $cabanas->map(function ($cabana) {
            
            // Format image url safely
            $imageUrl = null;
            $imagesCollection = $cabana->images;
            if ($imagesCollection && $imagesCollection->isNotEmpty()) {
                // $firstImage is a CabanaImage model — access the image_path attribute
                $firstImagePath = $imagesCollection->first()->image_path;
                if (filter_var($firstImagePath, FILTER_VALIDATE_URL)) {
                    $imageUrl = $firstImagePath;
                } else {
                    $imageUrl = asset('storage/' . ltrim($firstImagePath, '/'));
                }
            } else {
                // Fallback placeholder directly via asset
                $imageUrl = asset('images/cabana-placeholder.jpg'); 
            }

            return [
                'id' => $cabana->id,
                'name' => $cabana->name,
                'price' => $cabana->price_per_night,
                'image' => $imageUrl
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $formatted
        ]);
    }
}

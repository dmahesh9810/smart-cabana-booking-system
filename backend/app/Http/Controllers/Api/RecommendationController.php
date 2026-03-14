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
     * Public endpoint: return top 3 most-booked cabanas (popularity-based).
     */
    public function index(): JsonResponse
    {
        $popularCabanaIds = DB::table('bookings')
            ->select('cabana_id', DB::raw('COUNT(*) as total_bookings'))
            ->where('status', 'confirmed')
            ->groupBy('cabana_id')
            ->orderByDesc('total_bookings')
            ->limit(3)
            ->pluck('cabana_id');

        if ($popularCabanaIds->isEmpty()) {
            $cabanas = Cabana::where('is_active', true)->with('images')->inRandomOrder()->take(3)->get();
        } else {
            // Fetch all matching cabanas, then re-sort in PHP to preserve score order.
            // This is DB-agnostic: works on both SQLite (dev) and MySQL (production).
            $unsorted = Cabana::whereIn('id', $popularCabanaIds)
                ->where('is_active', true)
                ->with('images')
                ->get();

            // Restore popularity order by mapping over the ordered ID list
            $cabanas = collect($popularCabanaIds)
                ->map(fn($id) => $unsorted->firstWhere('id', $id))
                ->filter()
                ->values();
        }

        return response()->json([
            'success' => true,
            'data'    => $cabanas->map(fn($c) => $this->formatCabana($c)),
        ]);
    }

    /**
     * Protected endpoint: Personalized recommendations for the logged-in user.
     *
     * Algorithm (collaborative-filtering lite):
     *  1. Get cabana IDs the user has already booked.
     *  2. Find other users who booked those same cabanas ("similar users").
     *  3. Get all cabanas those similar users booked — score by frequency.
     *  4. Blend with top-rated and most-booked cabanas.
     *  5. Exclude already-booked cabanas by this user.
     *  6. Return top 4 with a human-readable reason.
     */
    public function personalized(Request $request): JsonResponse
    {
        $userId = $request->user()->id;

        // ── Step 1: User's already-booked cabana IDs ────────────────────
        $userBookedIds = DB::table('bookings')
            ->where('user_id', $userId)
            ->whereNull('deleted_at')
            ->pluck('cabana_id')
            ->unique()
            ->values();

        $scores = [];  // cabana_id => ['score' => int, 'reason' => string]

        // ── Step 2 & 3: Collaborative filtering ─────────────────────────
        if ($userBookedIds->isNotEmpty()) {
            // Find users who booked the same cabanas (not the current user)
            $similarUserIds = DB::table('bookings')
                ->whereIn('cabana_id', $userBookedIds)
                ->where('user_id', '!=', $userId)
                ->whereNull('deleted_at')
                ->pluck('user_id')
                ->unique();

            if ($similarUserIds->isNotEmpty()) {
                // Get what those similar users booked
                $collaborativeCabanas = DB::table('bookings')
                    ->select('cabana_id', DB::raw('COUNT(*) as freq'))
                    ->whereIn('user_id', $similarUserIds)
                    ->whereNotIn('cabana_id', $userBookedIds)
                    ->whereNull('deleted_at')
                    ->groupBy('cabana_id')
                    ->orderByDesc('freq')
                    ->get();

                foreach ($collaborativeCabanas as $row) {
                    $scores[$row->cabana_id] = [
                        'score'  => $row->freq * 3,   // weight: collaborative is strongest signal
                        'reason' => 'Guests who booked similar cabanas also loved this',
                    ];
                }
            }
        }

        // ── Step 4a: Blend with top-rated cabanas ────────────────────────
        $topRated = DB::table('reviews')
            ->select('cabana_id', DB::raw('AVG(rating) as avg_rating'), DB::raw('COUNT(*) as cnt'))
            ->groupBy('cabana_id')
            ->having('cnt', '>=', 1)
            ->orderByDesc('avg_rating')
            ->limit(8)
            ->get();

        $bookedArr = $userBookedIds->toArray();
        foreach ($topRated as $row) {
            if (in_array($row->cabana_id, $bookedArr)) continue;
            $existing = $scores[$row->cabana_id] ?? ['score' => 0, 'reason' => 'Highly rated by guests'];
            $scores[$row->cabana_id] = [
                'score'  => $existing['score'] + (int) round($row->avg_rating * 2),
                'reason' => $existing['score'] > 0 ? $existing['reason'] : 'Highly rated by guests',
            ];
        }

        // ── Step 4b: Blend with most-booked cabanas ─────────────────────
        $mostBooked = DB::table('bookings')
            ->select('cabana_id', DB::raw('COUNT(*) as total'))
            ->whereIn('status', ['confirmed', 'completed'])
            ->whereNotIn('cabana_id', $userBookedIds->toArray())
            ->whereNull('deleted_at')
            ->groupBy('cabana_id')
            ->orderByDesc('total')
            ->limit(8)
            ->get();

        foreach ($mostBooked as $row) {
            $existing = $scores[$row->cabana_id] ?? ['score' => 0, 'reason' => 'Trending — most booked right now'];
            $scores[$row->cabana_id] = [
                'score'  => $existing['score'] + $row->total,
                'reason' => $existing['score'] > 0 ? $existing['reason'] : 'Trending — most booked right now',
            ];
        }

        // ── Step 5: Exclude already-booked cabanas ─────────────────────
        foreach ($userBookedIds as $id) {
            unset($scores[$id]);
        }

        // ── Step 6: Sort by score, take top 4 ──────────────────────────
        arsort($scores);
        $topIds = array_keys(array_slice($scores, 0, 4, true));

        // Fallback: if no scores, return active cabanas
        if (empty($topIds)) {
            $cabanas = Cabana::where('is_active', true)
                ->whereNotIn('id', $userBookedIds->toArray())
                ->with(['images', 'reviews'])
                ->inRandomOrder()
                ->take(4)
                ->get();

            return response()->json([
                'recommended_cabanas' => $cabanas->map(function ($c) {
                    return $this->formatPersonalized($c, 'Popular destinations for new guests');
                }),
            ]);
        }

        // Fetch full Eloquent models, then re-sort in PHP to preserve score order.
        // This is DB-agnostic: works on both SQLite (dev) and MySQL (production).
        $unsorted = Cabana::whereIn('id', $topIds)
            ->where('is_active', true)
            ->with(['images', 'reviews'])
            ->get();

        // Restore score order by mapping over the pre-sorted $topIds array
        $cabanas = collect($topIds)
            ->map(fn($id) => $unsorted->firstWhere('id', $id))
            ->filter()
            ->values();

        return response()->json([
            'recommended_cabanas' => $cabanas->map(function ($cabana) use ($scores) {
                $reason = $scores[$cabana->id]['reason'] ?? 'Recommended for you';
                return $this->formatPersonalized($cabana, $reason);
            }),
        ]);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    private function resolveImageUrl(?string $path): ?string
    {
        if (!$path) return null;
        return filter_var($path, FILTER_VALIDATE_URL)
            ? $path
            : asset('storage/' . ltrim($path, '/'));
    }

    private function formatCabana(Cabana $cabana): array
    {
        $path = $cabana->images?->first()?->image_path;
        return [
            'id'    => $cabana->id,
            'name'  => $cabana->name,
            'price' => $cabana->price_per_night,
            'image' => $this->resolveImageUrl($path),
        ];
    }

    private function formatPersonalized(Cabana $cabana, string $reason): array
    {
        $path      = $cabana->images?->first()?->image_path;
        $avgRating = $cabana->reviews?->avg('rating');

        return [
            'id'       => $cabana->id,
            'name'     => $cabana->name,
            'price'    => $cabana->price_per_night,
            'location' => $cabana->location,
            'image'    => $this->resolveImageUrl($path),
            'rating'   => $avgRating ? round((float) $avgRating, 1) : null,
            'reason'   => $reason,
        ];
    }
}

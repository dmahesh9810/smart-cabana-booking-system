<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminBookingResource;
use App\Http\Resources\AdminPaymentResource;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    private \App\Services\SystemActivityService $dashboardService;

    public function __construct(\App\Services\SystemActivityService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Get statistics for the admin dashboard
     * 
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = $this->dashboardService->getStats();

        return response()->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

}

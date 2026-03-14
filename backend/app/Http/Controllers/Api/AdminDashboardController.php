<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SystemActivityService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AdminDashboardController extends Controller
{
    use ApiResponse;

    private SystemActivityService $dashboardService;

    public function __construct(SystemActivityService $dashboardService)
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
        $stats = Cache::remember('admin_dashboard_stats', 300, function () {
            return $this->dashboardService->getStats();
        });

        return $this->successResponse($stats, 'Dashboard statistics retrieved successfully');
    }
}

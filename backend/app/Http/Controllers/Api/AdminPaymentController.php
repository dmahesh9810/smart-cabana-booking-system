<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPaymentResource;
use App\Services\SystemActivityService;
use Illuminate\Http\Request;

use App\Traits\ApiResponse;

class AdminPaymentController extends Controller
{
    use ApiResponse;

    private \App\Services\SystemActivityService $dashboardService;

    public function __construct(SystemActivityService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * Display a paginated list of payments for admins
     */
    public function index()
    {
        $payments = $this->dashboardService->getPayments();
        return $this->successResponse(AdminPaymentResource::collection($payments), 'Payments retrieved successfully');
    }
}

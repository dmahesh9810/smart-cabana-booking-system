<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AdminPaymentResource;
use App\Services\SystemActivityService;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
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
        return AdminPaymentResource::collection($payments);
    }
}

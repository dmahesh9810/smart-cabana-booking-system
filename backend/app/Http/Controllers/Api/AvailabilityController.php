<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AvailabilityService;
use App\Http\Requests\CheckAvailabilityRequest;
use Illuminate\Http\JsonResponse;

class AvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    public function check(CheckAvailabilityRequest $request, string $id): JsonResponse
    {
        $isAvailable = $this->availabilityService->isAvailable(
            $id,
            $request->validated('check_in'),
            $request->validated('check_out')
        );

        return response()->json([
            'success' => true,
            'available' => $isAvailable
        ]);
    }

    public function calendar(string $id): JsonResponse
    {
        $data = $this->availabilityService->getCalendar($id);

        return response()->json([
            'success' => true,
            'data' => $data
        ]);
    }
}

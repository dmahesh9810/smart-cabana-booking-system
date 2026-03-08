<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BlockDatesRequest;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;

class AdminAvailabilityController extends Controller
{
    protected $availabilityService;

    public function __construct(AvailabilityService $availabilityService)
    {
        $this->availabilityService = $availabilityService;
    }

    /**
     * Block dates for a given cabana.
     */
    public function blockDates(BlockDatesRequest $request, string $id): JsonResponse
    {
        try {
            $block = $this->availabilityService->blockDates(
                $id,
                $request->validated('start_date'),
                $request->validated('end_date'),
                $request->validated('reason')
            );

            return response()->json([
                'success' => true,
                'message' => 'Dates blocked successfully',
                'data' => $block
            ], 201);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Remove an existing date block.
     */
    public function removeBlock(string $id): JsonResponse
    {
        $this->availabilityService->removeBlock((int)$id);

        return response()->json([
            'success' => true,
            'message' => 'Block removed successfully'
        ]);
    }
}

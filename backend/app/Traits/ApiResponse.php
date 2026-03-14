<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Return a standardized success JSON response.
     */
    protected function successResponse($data = null, string $message = null, int $code = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => $data,
            'message' => $message,
        ], $code);
    }

    /**
     * Return a standardized error JSON response.
     */
    protected function errorResponse(string $message = null, int $code = 400, $data = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'data'    => $data,
            'message' => $message,
        ], $code);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CabanaResource;
use App\Services\CabanaService;
use Illuminate\Http\JsonResponse;

class CabanaController extends Controller
{
    protected $cabanaService;

    public function __construct(CabanaService $cabanaService)
    {
        $this->cabanaService = $cabanaService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $cabanas = $this->cabanaService->getAllCabanas(false);
        return response()->json([
            'success' => true,
            'data' => CabanaResource::collection($cabanas)
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);

        if (!$cabana->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Cabana not found or inactive.'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => new CabanaResource($cabana)
        ]);
    }

    /**
     * Display the reviews for the specified cabana.
     */
    public function reviews(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);

        return response()->json([
            'success' => true,
            'data' => $cabana->reviews
        ]);
    }
}

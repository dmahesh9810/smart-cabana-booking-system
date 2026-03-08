<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCabanaRequest;
use App\Http\Requests\UpdateCabanaRequest;
use App\Http\Requests\UploadCabanaImageRequest;
use App\Http\Requests\SyncCabanaAmenitiesRequest;
use App\Http\Resources\CabanaResource;
use App\Services\CabanaService;
use Illuminate\Http\JsonResponse;

class AdminCabanaController extends Controller
{
    protected $cabanaService;

    public function __construct(CabanaService $cabanaService)
    {
        $this->cabanaService = $cabanaService;
    }

    /**
     * Display a listing of the cabanas.
     */
    public function index(): JsonResponse
    {
        $cabanas = $this->cabanaService->getAllCabanas(true);
        return response()->json([
            'success' => true,
            'data' => CabanaResource::collection($cabanas)
        ]);
    }

    /**
     * Store a newly created cabana in storage.
     */
    public function store(StoreCabanaRequest $request): JsonResponse
    {
        $cabana = $this->cabanaService->createCabana($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cabana created successfully',
            'data' => new CabanaResource($cabana)
        ], 201);
    }

    /**
     * Display the specified cabana.
     */
    public function show(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);

        return response()->json([
            'success' => true,
            'data' => new CabanaResource($cabana)
        ]);
    }

    /**
     * Update the specified cabana in storage.
     */
    public function update(UpdateCabanaRequest $request, string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $updatedCabana = $this->cabanaService->updateCabana($cabana, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Cabana updated successfully',
            'data' => new CabanaResource($updatedCabana)
        ]);
    }

    /**
     * Remove the specified cabana from storage.
     */
    public function destroy(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $this->cabanaService->deleteCabana($cabana);

        return response()->json([
            'success' => true,
            'message' => 'Cabana deleted successfully'
        ]);
    }

    /**
     * Upload an image for a cabana.
     */
    public function uploadImage(UploadCabanaImageRequest $request, string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $isPrimary = $request->boolean('is_primary', false);

        $image = $this->cabanaService->uploadImage($cabana, $request->file('image'), $isPrimary);

        return response()->json([
            'success' => true,
            'message' => 'Image uploaded successfully',
            'data' => $image
        ], 201);
    }

    /**
     * Delete an image.
     */
    public function deleteImage(string $id): JsonResponse
    {
        $this->cabanaService->deleteImage($id);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully'
        ]);
    }

    /**
     * Sync amenities for a cabana.
     */
    public function syncAmenities(SyncCabanaAmenitiesRequest $request, string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $this->cabanaService->syncAmenities($cabana, $request->input('amenities', []));

        return response()->json([
            'success' => true,
            'message' => 'Amenities synced successfully',
            'data' => new CabanaResource($cabana)
        ]);
    }
}

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
use Illuminate\Http\Request;

class AdminCabanaController extends Controller
{
    protected $cabanaService;

    public function __construct(CabanaService $cabanaService)
    {
        $this->cabanaService = $cabanaService;
    }

    /**
     * Display a listing of all cabanas (including inactive).
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
     * Store a newly created cabana, with optional inline image upload.
     */
    public function store(StoreCabanaRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Remove image from validated data before creating cabana
        $imageFile = $request->file('image');
        unset($data['image']);

        $cabana = $this->cabanaService->createCabana($data);

        // Upload primary image if provided
        if ($imageFile) {
            $this->cabanaService->uploadImage($cabana, $imageFile, true);
            $cabana->load('images', 'primaryImage');
        }

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
     * Update the specified cabana, with optional inline image replacement.
     */
    public function update(UpdateCabanaRequest $request, string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $data = $request->validated();

        // Remove image from validated data before updating cabana
        $imageFile = $request->file('image');
        unset($data['image']);

        $updatedCabana = $this->cabanaService->updateCabana($cabana, $data);

        // Replace primary image if a new file was provided
        if ($imageFile) {
            $this->cabanaService->uploadImage($updatedCabana, $imageFile, true);
            $updatedCabana->load('images', 'primaryImage');
        }

        return response()->json([
            'success' => true,
            'message' => 'Cabana updated successfully',
            'data' => new CabanaResource($updatedCabana)
        ]);
    }

    /**
     * Remove the specified cabana from storage (also deletes stored images).
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
     * Toggle the active/inactive status of a cabana.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $updated = $this->cabanaService->toggleStatus($cabana);

        return response()->json([
            'success' => true,
            'message' => $updated->is_active ? 'Cabana activated successfully' : 'Cabana deactivated successfully',
            'data' => new CabanaResource($updated)
        ]);
    }

    /**
     * Upload an image for a specific cabana.
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
     * Delete an image by image ID.
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

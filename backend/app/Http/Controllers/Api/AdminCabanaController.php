<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCabanaRequest;
use App\Http\Requests\UpdateCabanaRequest;
use App\Http\Requests\UploadCabanaImageRequest;
use App\Http\Requests\SyncCabanaAmenitiesRequest;
use App\Http\Resources\CabanaResource;
use App\Services\CabanaService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AdminCabanaController extends Controller
{
    use ApiResponse;

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
        return $this->successResponse(CabanaResource::collection($cabanas), 'Cabanas retrieved successfully');
    }

    /**
     * Store a newly created cabana, with optional inline image upload.
     */
    public function store(CreateCabanaRequest $request): JsonResponse
    {
        $data = $request->validated();

        // Remove image from validated data before creating cabana
        $imageFile = $request->file('image');
        unset($data['image']);

        $cabana = $this->cabanaService->createCabana($data);
        Cache::forget('admin_dashboard_stats');

        // Upload primary image if provided
        if ($imageFile) {
            $this->cabanaService->uploadImage($cabana, $imageFile, true);
            $cabana->load('images', 'primaryImage');
        }

        return $this->successResponse(new CabanaResource($cabana), 'Cabana created successfully', 201);
    }

    /**
     * Display the specified cabana.
     */
    public function show(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);

        return $this->successResponse(new CabanaResource($cabana), 'Cabana details retrieved successfully');
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
        Cache::forget('admin_dashboard_stats');

        // Replace primary image if a new file was provided
        if ($imageFile) {
            $this->cabanaService->uploadImage($updatedCabana, $imageFile, true);
            $updatedCabana->load('images', 'primaryImage');
        }

        return $this->successResponse(new CabanaResource($updatedCabana), 'Cabana updated successfully');
    }

    /**
     * Remove the specified cabana from storage (also deletes stored images).
     */
    public function destroy(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $this->cabanaService->deleteCabana($cabana);
        Cache::forget('admin_dashboard_stats');

        return $this->successResponse(null, 'Cabana deleted successfully');
    }

    /**
     * Toggle the active/inactive status of a cabana.
     */
    public function toggleStatus(string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $updated = $this->cabanaService->toggleStatus($cabana);
        Cache::forget('admin_dashboard_stats');

        $message = $updated->is_active ? 'Cabana activated successfully' : 'Cabana deactivated successfully';
        return $this->successResponse(new CabanaResource($updated), $message);
    }

    /**
     * Upload an image for a specific cabana.
     */
    public function uploadImage(UploadCabanaImageRequest $request, string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $isPrimary = $request->boolean('is_primary', false);

        $image = $this->cabanaService->uploadImage($cabana, $request->file('image'), $isPrimary);
        Cache::forget('admin_dashboard_stats');

        return $this->successResponse($image, 'Image uploaded successfully', 201);
    }

    /**
     * Delete an image by image ID.
     */
    public function deleteImage(string $id): JsonResponse
    {
        $this->cabanaService->deleteImage($id);
        Cache::forget('admin_dashboard_stats');

        return $this->successResponse(null, 'Image deleted successfully');
    }

    /**
     * Sync amenities for a cabana.
     */
    public function syncAmenities(SyncCabanaAmenitiesRequest $request, string $id): JsonResponse
    {
        $cabana = $this->cabanaService->getCabanaById($id);
        $this->cabanaService->syncAmenities($cabana, $request->input('amenities', []));

        return $this->successResponse(new CabanaResource($cabana), 'Amenities synced successfully');
    }
}

<?php

namespace App\Services;

use App\Models\Cabana;
use App\Models\CabanaImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class CabanaService
{
    public function getAllCabanas(bool $admin = true)
    {
        // Load relationships requested by the user
        $query = Cabana::with(['primaryImage', 'images', 'amenities', 'reviews']);

        if (!$admin) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function getCabanaById(int $id)
    {
        return Cabana::with(['images', 'amenities', 'reviews'])->findOrFail($id);
    }

    public function createCabana(array $data)
    {
        return Cabana::create($data);
    }

    public function updateCabana(Cabana $cabana, array $data)
    {
        $cabana->update($data);
        return $cabana;
    }

    public function deleteCabana(Cabana $cabana)
    {
        // Optional: you can choose to delete images from storage,
        // or just let SoftDeletes handle the record deletion.
        $cabana->delete();
        return true;
    }

    public function uploadImage(Cabana $cabana, UploadedFile $file, bool $isPrimary = false)
    {
        $path = $file->store('cabanas/' . $cabana->id, 'public');

        if ($isPrimary) {
            // Unset other primary images for this cabana
            $cabana->images()->update(['is_primary' => false]);
        }

        return $cabana->images()->create([
            'image_path' => $path,
            'is_primary' => $isPrimary
        ]);
    }

    public function deleteImage(int $imageId)
    {
        $image = CabanaImage::findOrFail($imageId);
        Storage::disk('public')->delete($image->image_path);

        $image->delete();
        return true;
    }

    public function syncAmenities(Cabana $cabana, array $amenityIds)
    {
        $cabana->amenities()->sync($amenityIds);
        return $cabana->load('amenities');
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SyncCabanaAmenitiesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'amenities' => ['required', 'array'],
            'amenities.*' => ['integer', 'exists:amenities,id']
        ];
    }
}

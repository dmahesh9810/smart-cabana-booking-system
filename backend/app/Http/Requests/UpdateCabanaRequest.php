<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCabanaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'required', 'string'],
            'price_per_night' => ['sometimes', 'required', 'numeric', 'min:0'],
            'max_guests' => ['sometimes', 'required', 'integer', 'min:1'],
            'location' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean']
        ];
    }
}

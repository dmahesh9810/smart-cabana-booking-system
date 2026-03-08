<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BlockDatesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() && $this->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'reason' => ['required', 'string', 'max:500'],
        ];
    }
}

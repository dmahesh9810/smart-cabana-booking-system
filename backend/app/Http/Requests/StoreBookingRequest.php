<?php

namespace App\Http\Requests;

use App\Models\Cabana;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'cabana_id' => ['required', 'integer', 'exists:cabanas,id'],
            'check_in' => ['required', 'date', 'after_or_equal:today'],
            'check_out' => ['required', 'date', 'after:check_in'],
            'guests_count' => ['required', 'integer', 'min:1'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if ($this->cabana_id && $this->guests_count) {
                $cabana = Cabana::find($this->cabana_id);
                if ($cabana && $this->guests_count > $cabana->max_guests) {
                    $validator->errors()->add('guests_count', "The number of guests cannot exceed the cabana's maximum capacity of {$cabana->max_guests}.");
                }
            }
        });
    }
}

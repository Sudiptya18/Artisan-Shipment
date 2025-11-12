<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOriginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $originId = $this->route('origin')?->id;

        return [
            'origin_name' => ['required', 'string', 'max:128', 'unique:origins,origin_name,' . $originId],
            'iso_code' => ['nullable', 'string', 'max:8'],
        ];
    }
}

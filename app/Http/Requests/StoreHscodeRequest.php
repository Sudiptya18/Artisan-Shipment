<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHscodeRequest extends FormRequest
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
        $hscodeId = $this->route('hscode')?->id;

        return [
            'hscode' => ['required', 'string', 'max:50', 'unique:hscodes,hscode,' . $hscodeId],
            'description' => ['nullable', 'string'],
            'cd' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'rd' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'sd' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'vat' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'ait' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'at' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'exd' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'tti' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'min_ass_value' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
        ];
    }
}


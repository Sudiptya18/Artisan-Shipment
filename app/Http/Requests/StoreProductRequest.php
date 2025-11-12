<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
        return [
            'sku' => ['nullable', 'string', 'max:128', 'unique:products,sku'],
            'product_title' => ['required', 'string', 'max:512'],
            'global_code' => ['nullable', 'string', 'max:128'],
            'description' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'pack_size' => ['nullable', 'string', 'max:128'],
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'format_id' => ['nullable', 'exists:formats,id'],
            'origin_id' => ['nullable', 'exists:origins,id'],
            'active' => ['sometimes', 'boolean'],
            'images' => ['sometimes', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'max:5120'],
        ];
    }
}

<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductDetailRequest extends FormRequest
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
            'product_id' => ['required', 'exists:products,id'],
            'pcs_cases' => ['nullable', 'string', 'max:255'],
            'cases_pal' => ['nullable', 'string', 'max:255'],
            'cases_lay' => ['nullable', 'string', 'max:255'],
            'container_load' => ['nullable', 'string', 'max:255'],
            'cases_20ft_container' => ['nullable', 'string', 'max:255'],
            'cases_40ft_container' => ['nullable', 'string', 'max:255'],
            'total_shelf_life' => ['nullable', 'string', 'max:255'],
            'gross_weight_cs_kg' => ['nullable', 'numeric', 'min:0'],
            'net_weight_cs_kg' => ['nullable', 'numeric', 'min:0'],
            'cbm' => ['nullable', 'numeric', 'min:0'],
            'hs_code_id' => ['nullable', 'exists:hscodes,id'],
            'rate' => ['nullable', 'numeric', 'min:0'],
            'shipment_title_id' => ['nullable', 'exists:titles,id'],
            'commodity_id' => ['nullable', 'exists:commodities,id'],
        ];
    }
}

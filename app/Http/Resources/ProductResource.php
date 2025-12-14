<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_title' => $this->product_title,
            'global_code' => $this->global_code,
            'description' => $this->description,
            'benefits' => $this->benefits,
            'pack_size' => $this->pack_size,
            'status' => $this->status,
            'active' => (bool) $this->active,
            'brand' => $this->whenLoaded('brand', fn () => [
                'id' => $this->brand?->id,
                'name' => $this->brand?->brand_name,
            ]),
            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category?->id,
                'name' => $this->category?->category_name,
                'parent_id' => $this->category?->parent_id,
            ]),
            'format' => $this->whenLoaded('format', fn () => [
                'id' => $this->format?->id,
                'name' => $this->format?->format_name,
            ]),
            'origin' => $this->whenLoaded('origin', fn () => [
                'id' => $this->origin?->id,
                'name' => $this->origin?->origin_name,
                'iso_code' => $this->origin?->iso_code,
            ]),
            'images' => $this->whenLoaded('images', fn () => $this->images->map(fn ($image) => [
                'id' => $image->id,
                'url' => $image->image_url ? Storage::disk('public')->url($image->image_url) : null,
                'path' => $image->image_url,
                'alt_text' => $image->alt_text,
                'position' => $image->position,
            ])),
            'product_detail' => $this->whenLoaded('productDetail', fn () => [
                'id' => $this->productDetail->id,
                'pcs_cases' => $this->productDetail->pcs_cases,
                'cases_pal' => $this->productDetail->cases_pal,
                'cases_lay' => $this->productDetail->cases_lay,
                'container_load' => $this->productDetail->containerLoad?->name,
                'container_load_id' => $this->productDetail->container_load_id,
                'cases_20ft_container' => $this->productDetail->cases_20ft_container,
                'cases_40ft_container' => $this->productDetail->cases_40ft_container,
                'total_shelf_life' => $this->productDetail->total_shelf_life,
                'gross_weight_cs_kg' => $this->productDetail->gross_weight_cs_kg,
                'net_weight_cs_kg' => $this->productDetail->net_weight_cs_kg,
                'cbm' => $this->productDetail->cbm,
                'hs_code' => $this->productDetail->hsCode?->hscode,
                'hs_code_id' => $this->productDetail->hs_code_id,
                'rate' => $this->productDetail->rate,
                'shipment_title' => $this->productDetail->shipmentTitle?->name,
                'shipment_title_id' => $this->productDetail->shipment_title_id,
                'commodity' => $this->productDetail->commodity?->name,
                'commodity_id' => $this->productDetail->commodity_id,
            ]),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

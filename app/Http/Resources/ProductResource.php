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
            'sku' => $this->sku,
            'product_title' => $this->product_title,
            'global_code' => $this->global_code,
            'description' => $this->description,
            'benefits' => $this->benefits,
            'pack_size' => $this->pack_size,
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
                'alt_text' => $image->alt_text,
                'position' => $image->position,
            ])),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}

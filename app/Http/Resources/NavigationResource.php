<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NavigationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'key' => $this->key,
            'title' => $this->title,
            'route' => $this->route,
            'url' => $this->url,
            'icon' => $this->icon,
            'order' => $this->order_by,
            'is_enabled' => $this->is_enabled,
            'is_visible' => $this->is_visible,
            'children' => NavigationResource::collection(
                $this->whenLoaded('children')
            ),
        ];
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Format;
use App\Models\Origin;
use Illuminate\Http\Request;

class ProductLookupController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'brands' => Brand::query()
                ->orderBy('brand_name')
                ->get()
                ->map(function ($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->brand_name,
                    ];
                }),
            'categories' => Category::query()
                ->orderBy('category_name')
                ->get()
                ->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->category_name,
                        'parent_id' => $category->parent_id,
                    ];
                }),
            'formats' => Format::query()
                ->orderBy('format_name')
                ->get()
                ->map(function ($format) {
                    return [
                        'id' => $format->id,
                        'name' => $format->format_name,
                    ];
                }),
            'origins' => Origin::query()
                ->orderBy('origin_name')
                ->get()
                ->map(function ($origin) {
                    return [
                        'id' => $origin->id,
                        'name' => $origin->origin_name,
                        'iso_code' => $origin->iso_code,
                    ];
                }),
        ]);
    }
}

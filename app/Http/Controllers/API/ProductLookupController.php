<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Format;
use App\Models\Origin;
use App\Models\Hscode;
use App\Models\Title;
use App\Models\Commodity;
use App\Models\ContainerLoad;
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
            'hscodes' => Hscode::query()
                ->orderBy('hscode')
                ->get()
                ->map(function ($hscode) {
                    return [
                        'id' => $hscode->id,
                        'hscode' => $hscode->hscode,
                    ];
                }),
            'titles' => Title::query()
                ->orderBy('name')
                ->get()
                ->map(function ($title) {
                    return [
                        'id' => $title->id,
                        'name' => $title->name,
                    ];
                }),
            'commodities' => Commodity::query()
                ->orderBy('name')
                ->get()
                ->map(function ($commodity) {
                    return [
                        'id' => $commodity->id,
                        'name' => $commodity->name,
                    ];
                }),
            'container_loads' => ContainerLoad::query()
                ->orderBy('name')
                ->get()
                ->map(function ($containerLoad) {
                    return [
                        'id' => $containerLoad->id,
                        'name' => $containerLoad->name,
                    ];
                }),
        ]);
    }
}

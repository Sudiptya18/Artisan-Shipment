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
                ->get(['id', 'brand_name as name']),
            'categories' => Category::query()
                ->orderBy('category_name')
                ->get(['id', 'category_name as name', 'parent_id']),
            'formats' => Format::query()
                ->orderBy('format_name')
                ->get(['id', 'format_name as name']),
            'origins' => Origin::query()
                ->orderBy('origin_name')
                ->get(['id', 'origin_name as name', 'iso_code']),
        ]);
    }
}

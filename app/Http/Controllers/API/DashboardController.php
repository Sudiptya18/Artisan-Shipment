<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Format;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function totalProducts()
    {
        $total = Product::count();
        return response()->json(['total' => $total]);
    }

    public function totalBrands()
    {
        $total = Brand::count();
        return response()->json(['total' => $total]);
    }

    public function totalCategories()
    {
        $total = Category::count();
        return response()->json(['total' => $total]);
    }

    public function totalFormats()
    {
        $total = Format::count();
        return response()->json(['total' => $total]);
    }

    public function productsByBrand()
    {
        $productsByBrand = Brand::withCount('products')
            ->orderBy('products_count', 'desc')
            ->get()
            ->map(function ($brand) {
                return [
                    'name' => $brand->brand_name,
                    'count' => $brand->products_count,
                ];
            });

        return response()->json(['data' => $productsByBrand]);
    }
}


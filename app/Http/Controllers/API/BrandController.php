<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBrandRequest;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Brand::orderBy('brand_name')->get();
        return response()->json(['data' => $brands]);
    }

    public function store(StoreBrandRequest $request)
    {
        $data = $request->validated();

        $brand = DB::transaction(function () use ($data) {
            return Brand::create($data);
        });

        return response()->json(['data' => $brand], 201);
    }

    public function show(Brand $brand)
    {
        return response()->json(['data' => $brand]);
    }

    public function update(StoreBrandRequest $request, Brand $brand)
    {
        $data = $request->validated();

        DB::transaction(function () use ($brand, $data) {
            $brand->update($data);
        });

        return response()->json(['data' => $brand->fresh()]);
    }

    public function destroy(Brand $brand)
    {
        DB::transaction(function () use ($brand) {
            $brand->delete();
        });

        return response()->noContent();
    }
}


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

        activity()
            ->performedOn($brand)
            ->withProperties(['page' => 'brands'])
            ->log("Brand created: {$brand->brand_name}");

        return response()->json(['data' => $brand], 201);
    }

    public function show(Brand $brand)
    {
        return response()->json(['data' => $brand]);
    }

    public function update(StoreBrandRequest $request, Brand $brand)
    {
        $data = $request->validated();
        $oldValues = $brand->toArray();

        DB::transaction(function () use ($brand, $data) {
            $brand->update($data);
        });

        activity()
            ->performedOn($brand)
            ->withProperties([
                'page' => 'brands',
                'old' => $oldValues,
                'attributes' => $brand->fresh()->toArray(),
            ])
            ->log("Brand updated: {$brand->brand_name}");

        return response()->json(['data' => $brand->fresh()]);
    }

    public function destroy(Brand $brand)
    {
        $brandName = $brand->brand_name;
        $brandId = $brand->id;
        
        DB::transaction(function () use ($brand) {
            $brand->delete();
        });

        activity()
            ->withProperties([
                'page' => 'brands',
                'deleted_id' => $brandId,
            ])
            ->log("Brand deleted: {$brandName}");

        return response()->noContent();
    }
}


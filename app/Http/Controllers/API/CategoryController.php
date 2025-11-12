<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategoryRequest;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::orderBy('category_name')->get();
        return response()->json(['data' => $categories]);
    }

    public function store(StoreCategoryRequest $request)
    {
        $data = $request->validated();

        $category = DB::transaction(function () use ($data) {
            return Category::create($data);
        });

        return response()->json(['data' => $category], 201);
    }

    public function show(Category $category)
    {
        return response()->json(['data' => $category]);
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        DB::transaction(function () use ($category, $data) {
            $category->update($data);
        });

        return response()->json(['data' => $category->fresh()]);
    }

    public function destroy(Category $category)
    {
        DB::transaction(function () use ($category) {
            $category->delete();
        });

        return response()->noContent();
    }
}

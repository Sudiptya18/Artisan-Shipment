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

        activity()
            ->performedOn($category)
            ->withProperties(['page' => 'categories'])
            ->log("Category created: {$category->category_name}");

        return response()->json(['data' => $category], 201);
    }

    public function show(Category $category)
    {
        return response()->json(['data' => $category]);
    }

    public function update(StoreCategoryRequest $request, Category $category)
    {
        $data = $request->validated();
        $oldValues = $category->toArray();

        DB::transaction(function () use ($category, $data) {
            $category->update($data);
        });

        activity()
            ->performedOn($category)
            ->withProperties([
                'page' => 'categories',
                'old' => $oldValues,
                'attributes' => $category->fresh()->toArray(),
            ])
            ->log("Category updated: {$category->category_name}");

        return response()->json(['data' => $category->fresh()]);
    }

    public function destroy(Category $category)
    {
        $categoryName = $category->category_name;
        $categoryId = $category->id;
        
        DB::transaction(function () use ($category) {
            $category->delete();
        });

        activity()
            ->withProperties([
                'page' => 'categories',
                'deleted_id' => $categoryId,
            ])
            ->log("Category deleted: {$categoryName}");

        return response()->noContent();
    }
}

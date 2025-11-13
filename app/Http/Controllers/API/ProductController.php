<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 20);
        $search = $request->input('search');
        $active = $request->input('active');

        $query = Product::query()
            ->with(['brand', 'category', 'format', 'origin', 'images'])
            ->orderByDesc('created_at');

        // General search (searches across multiple fields)
        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('product_title', 'like', "%{$search}%")
                    ->orWhere('global_code', 'like', "%{$search}%");
            });
        }

        if ($request->has('filter_global_code') && $request->input('filter_global_code')) {
            $query->where('global_code', 'like', "%{$request->input('filter_global_code')}%");
        }

        if ($request->has('filter_product_title') && $request->input('filter_product_title')) {
            $query->where('product_title', 'like', "%{$request->input('filter_product_title')}%");
        }

        if ($request->has('filter_brand_id') && $request->input('filter_brand_id')) {
            $query->where('brand_id', $request->input('filter_brand_id'));
        }

        if ($request->has('filter_category_id') && $request->input('filter_category_id')) {
            $query->where('category_id', $request->input('filter_category_id'));
        }

        if ($request->has('filter_format_id') && $request->input('filter_format_id')) {
            $query->where('format_id', $request->input('filter_format_id'));
        }

        if ($request->has('filter_origin_id') && $request->input('filter_origin_id')) {
            $query->where('origin_id', $request->input('filter_origin_id'));
        }

        if ($request->has('filter_active') && $request->input('filter_active') !== '') {
            $query->where('active', filter_var($request->input('filter_active'), FILTER_VALIDATE_BOOL));
        }

        // Legacy active filter (for backward compatibility)
        if ($active !== null && $active !== '' && !$request->has('filter_active')) {
            $query->where('active', filter_var($active, FILTER_VALIDATE_BOOL));
        }

        // If per_page is 0, return all records without pagination
        if ($perPage === 0) {
            $products = $query->get();
            return ProductResource::collection($products)->additional([
                'meta' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => $products->count(),
                    'total' => $products->count(),
                ],
            ]);
        }

        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        // Additional security: Sanitize input
        $data = $request->validated();
        
        // Sanitize string fields
        if (isset($data['product_title'])) {
            $data['product_title'] = strip_tags($data['product_title']);
        }
        if (isset($data['description'])) {
            $data['description'] = strip_tags($data['description']);
        }
        if (isset($data['benefits'])) {
            $data['benefits'] = strip_tags($data['benefits']);
        }
        if (isset($data['pack_size'])) {
            $data['pack_size'] = strip_tags($data['pack_size']);
        }
        if (isset($data['global_code'])) {
            $data['global_code'] = preg_replace('/[^a-zA-Z0-9]/', '', $data['global_code']);
        }
        
        $images = $data['images'] ?? [];
        unset($data['images']);

        // Set default status to ACTIVE if not provided
        if (!isset($data['status'])) {
            $data['status'] = 'ACTIVE';
        }

        // Set active to true by default
        if (!isset($data['active'])) {
            $data['active'] = true;
        }

        $product = DB::transaction(function () use ($data, $images) {
            $product = Product::create($data);

            $this->syncImages($product, $images);

            return $product;
        });

        // Log activity
        activity()
            ->performedOn($product)
            ->withProperties(['page' => 'products-create'])
            ->log("Product created: {$product->product_title} ({$product->global_code})");

        return (new ProductResource(
            $product->load(['brand', 'category', 'format', 'origin', 'images'])
        ))->response()->setStatusCode(201);
    }

    public function show(Product $product)
    {
        return new ProductResource(
            $product->load(['brand', 'category', 'format', 'origin', 'images'])
        );
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        // Additional security: Sanitize input
        $data = $request->validated();
        
        // Sanitize string fields
        if (isset($data['product_title'])) {
            $data['product_title'] = strip_tags($data['product_title']);
        }
        if (isset($data['description'])) {
            $data['description'] = strip_tags($data['description']);
        }
        if (isset($data['benefits'])) {
            $data['benefits'] = strip_tags($data['benefits']);
        }
        if (isset($data['pack_size'])) {
            $data['pack_size'] = strip_tags($data['pack_size']);
        }
        if (isset($data['global_code'])) {
            $data['global_code'] = preg_replace('/[^a-zA-Z0-9]/', '', $data['global_code']);
        }
        
        $images = $data['images'] ?? [];
        unset($data['images']);

        $oldValues = $product->toArray();

        $product = DB::transaction(function () use ($product, $data, $images) {
            $product->update($data);
            $this->syncImages($product, $images);
            return $product->fresh();
        });

        // Log activity
        activity()
            ->performedOn($product)
            ->withProperties([
                'page' => 'products-edit',
                'old' => $oldValues,
                'attributes' => $product->toArray(),
            ])
            ->log("Product updated: {$product->product_title} ({$product->global_code})");

        return new ProductResource(
            $product->load(['brand', 'category', 'format', 'origin', 'images'])
        );
    }

    public function destroy(Product $product)
    {
        $productTitle = $product->product_title;
        $globalCode = $product->global_code;

        DB::transaction(function () use ($product) {
            // Delete all images
            foreach ($product->images as $image) {
                if ($image->image_url) {
                    Storage::disk('public')->delete($image->image_url);
                }
            }
            $product->delete();
        });

        // Log activity
        activity()
            ->withProperties([
                'page' => 'products-list',
                'deleted_id' => $product->id,
            ])
            ->log("Product deleted: {$productTitle} ({$globalCode})");

        return response()->noContent();
    }

    public function storeImages(Product $product, Request $request)
    {
        $validated = $request->validate([
            'images' => ['required', 'array', 'max:10'],
            'images.*' => ['file', 'image', 'max:5120'],
        ]);

        DB::transaction(function () use ($product, $validated) {
            $this->syncImages($product, $validated['images']);
        });

        return new ProductResource(
            $product->fresh()->load(['brand', 'category', 'format', 'origin', 'images'])
        );
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);

        DB::transaction(function () use ($image) {
            if ($image->image_url) {
                Storage::disk('public')->delete($image->image_url);
            }
            $image->delete();
        });

        return response()->noContent();
    }

    protected function syncImages(Product $product, array $images): void
    {
        foreach ($images as $index => $file) {
            if (!$file instanceof UploadedFile) {
                continue;
            }

            // Store in public/assets/img/products
            $path = $file->storeAs('assets/img/products', $file->hashName(), 'public');

            $product->images()->create([
                'image_url' => $path,
                'alt_text' => $product->product_title ?? 'Product Image',
                'position' => $index,
            ]);
        }
    }
}

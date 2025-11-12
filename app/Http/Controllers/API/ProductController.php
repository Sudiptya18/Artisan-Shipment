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
        $perPage = (int) $request->input('per_page', 15);
        $search = $request->input('search');
        $active = $request->input('active');

        $query = Product::query()
            ->with(['brand', 'category', 'format', 'origin', 'images'])
            ->orderByDesc('created_at');

        if ($search) {
            $query->where(function ($builder) use ($search) {
                $builder->where('product_title', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")
                    ->orWhere('global_code', 'like', "%{$search}%");
            });
        }

        if ($active !== null && $active !== '') {
            $query->where('active', filter_var($active, FILTER_VALIDATE_BOOL));
        }

        $products = $query->paginate($perPage);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $data = $request->validated();
        $images = $data['images'] ?? [];
        unset($data['images']);

        $product = DB::transaction(function () use ($data, $images) {
            $product = Product::create($data);

            $this->syncImages($product, $images);

            return $product;
        });

        return (new ProductResource(
            $product->load(['brand', 'category', 'format', 'origin', 'images'])
        ))->response()->setStatusCode(201);
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

            $path = $file->store('product-images', 'public');

            $product->images()->create([
                'image_url' => $path,
                'alt_text' => $product->product_title,
                'position' => $index,
            ]);
        }
    }
}

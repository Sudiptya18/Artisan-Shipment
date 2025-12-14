<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductDetail;
use App\Models\Hscode;
use App\Models\Title;
use App\Models\Commodity;
use App\Models\ContainerLoad;
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

        $productDetails = $data['product_details'] ?? null;
        unset($data['product_details']);

        $product = DB::transaction(function () use ($data, $images, $productDetails) {
            $product = Product::create($data);

            $this->syncImages($product, $images);
            $this->syncProductDetails($product, $productDetails);

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
            $product->load(['brand', 'category', 'format', 'origin', 'images', 'productDetail.hsCode', 'productDetail.shipmentTitle', 'productDetail.commodity', 'productDetail.containerLoad'])
        );
    }

    public function update(StoreProductRequest $request, Product $product)
    {
        // Additional security: Sanitize input
        $data = $request->validated();
        
        // Parse product_details if it's a JSON string
        if (isset($data['product_details']) && is_string($data['product_details'])) {
            $data['product_details'] = json_decode($data['product_details'], true) ?? [];
        }
        
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
        $productDetails = $data['product_details'] ?? null;
        unset($data['product_details']);

        $product = DB::transaction(function () use ($product, $data, $images, $productDetails) {
            $product->update($data);
            $this->syncImages($product, $images);
            $this->syncProductDetails($product, $productDetails);
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
            $product->load(['brand', 'category', 'format', 'origin', 'images', 'productDetail.hsCode', 'productDetail.shipmentTitle', 'productDetail.commodity', 'productDetail.containerLoad'])
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

    protected function syncProductDetails(Product $product, ?array $productDetails): void
    {
        if ($productDetails === null) {
            return;
        }

        // Resolve foreign keys
        $hsCodeId = null;
        if (!empty($productDetails['hs_code'])) {
            $hsCode = Hscode::where('hscode', $productDetails['hs_code'])->first();
            if ($hsCode) {
                $hsCodeId = $hsCode->id;
            }
        }

        $shipmentTitleId = null;
        if (!empty($productDetails['shipment_title'])) {
            $title = Title::where('name', $productDetails['shipment_title'])->first();
            if ($title) {
                $shipmentTitleId = $title->id;
            }
        }

        $commodityId = null;
        if (!empty($productDetails['commodity'])) {
            $commodity = Commodity::where('name', $productDetails['commodity'])->first();
            if ($commodity) {
                $commodityId = $commodity->id;
            }
        }

        $containerLoadId = null;
        if (!empty($productDetails['container_load'])) {
            $containerLoad = ContainerLoad::where('name', $productDetails['container_load'])->first();
            if ($containerLoad) {
                $containerLoadId = $containerLoad->id;
            }
        }

        $detailData = [
            'product_id' => $product->id,
            'pcs_cases' => $productDetails['pcs_cases'] ?? null,
            'cases_pal' => $productDetails['cases_pal'] ?? null,
            'cases_lay' => $productDetails['cases_lay'] ?? null,
            'container_load_id' => $containerLoadId,
            'cases_20ft_container' => $productDetails['cases_20ft_container'] ?? null,
            'cases_40ft_container' => $productDetails['cases_40ft_container'] ?? null,
            'total_shelf_life' => $productDetails['total_shelf_life'] ?? null,
            'gross_weight_cs_kg' => isset($productDetails['gross_weight_cs_kg']) ? (float) $productDetails['gross_weight_cs_kg'] : null,
            'net_weight_cs_kg' => isset($productDetails['net_weight_cs_kg']) ? (float) $productDetails['net_weight_cs_kg'] : null,
            'cbm' => isset($productDetails['cbm']) ? (float) $productDetails['cbm'] : null,
            'hs_code_id' => $hsCodeId,
            'rate' => isset($productDetails['rate']) ? (float) $productDetails['rate'] : null,
            'shipment_title_id' => $shipmentTitleId,
            'commodity_id' => $commodityId,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ];

        // Remove null values to avoid overwriting existing data
        $detailData = array_filter($detailData, function ($value) {
            return $value !== null && $value !== '';
        });

        if (empty($detailData)) {
            return;
        }

        $product->productDetail()->updateOrCreate(
            ['product_id' => $product->id],
            $detailData
        );
    }

    public function bulkStore(Request $request)
    {
        $request->validate([
            'products' => ['required', 'array', 'min:1'],
            'products.*.global_code' => ['required', 'string', 'max:128'],
            'products.*.product_title' => ['nullable', 'string', 'max:512'],
            'products.*.description' => ['nullable', 'string'],
            'products.*.benefits' => ['nullable', 'string'],
            'products.*.pack_size' => ['nullable', 'string', 'max:128'],
            'products.*.brand_id' => ['nullable', 'exists:brands,id'],
            'products.*.category_id' => ['nullable', 'exists:categories,id'],
            'products.*.format_id' => ['nullable', 'exists:formats,id'],
            'products.*.origin_id' => ['nullable', 'exists:origins,id'],
            'products.*.status' => ['nullable', 'in:ACTIVE,DISCONTINUED-UI,DISCONTINUED-ARTISAN,REPLACEMENT,REPLACEMENT & DISCONTINUED,NEW CODE,FUTURE DISCONTINUED,NEW TENTATIVE'],
            'products.*.active' => ['sometimes', 'boolean'],
        ]);

        $products = $request->input('products');
        $created = 0;
        $updated = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($products as $index => $productData) {
                try {
                    // Sanitize input
                    $sanitized = [
                        'global_code' => strip_tags(trim($productData['global_code'] ?? '')),
                        'product_title' => isset($productData['product_title']) ? strip_tags(trim($productData['product_title'])) : null,
                        'description' => isset($productData['description']) ? strip_tags(trim($productData['description'])) : null,
                        'benefits' => isset($productData['benefits']) ? strip_tags(trim($productData['benefits'])) : null,
                        'pack_size' => isset($productData['pack_size']) ? preg_replace('/[<>]/', '', trim($productData['pack_size'])) : null,
                        'brand_id' => $productData['brand_id'] ?? null,
                        'category_id' => $productData['category_id'] ?? null,
                        'format_id' => $productData['format_id'] ?? null,
                        'origin_id' => $productData['origin_id'] ?? null,
                        'status' => $productData['status'] ?? 'ACTIVE',
                        'active' => isset($productData['active']) ? filter_var($productData['active'], FILTER_VALIDATE_BOOLEAN) : true,
                    ];

                    if (empty($sanitized['global_code'])) {
                        continue;
                    }

                    $productId = $productData['id'] ?? null;

                    if ($productId) {
                        // Update existing product
                        $product = Product::find($productId);
                        if ($product) {
                            $product->update($sanitized);
                            $updated++;

                            // Log activity
                            activity()
                                ->causedBy($request->user())
                                ->performedOn($product)
                                ->withProperties(['page' => 'products-multiple-create'])
                                ->log("Updated product: {$product->product_title} ({$product->global_code})");
                        }
                    } else {
                        // Create new product - check if global_code already exists
                        $existing = Product::where('global_code', $sanitized['global_code'])->first();
                        if ($existing) {
                            // Update existing instead of creating duplicate
                            $existing->update($sanitized);
                            $updated++;

                            activity()
                                ->causedBy($request->user())
                                ->performedOn($existing)
                                ->withProperties(['page' => 'products-multiple-create'])
                                ->log("Updated product: {$existing->product_title} ({$existing->global_code})");
                        } else {
                            // Create new product
                            $product = Product::create($sanitized);
                            $created++;

                            // Log activity
                            activity()
                                ->causedBy($request->user())
                                ->performedOn($product)
                                ->withProperties(['page' => 'products-multiple-create'])
                                ->log("Created product: {$product->product_title} ({$product->global_code})");
                        }
                    }
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Successfully processed products.",
                'created' => $created,
                'updated' => $updated,
                'errors' => $errors,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save products: ' . $e->getMessage(),
            ], 500);
        }
    }
}

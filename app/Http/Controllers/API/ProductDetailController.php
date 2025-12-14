<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductDetail;
use App\Models\Hscode;
use App\Models\Title;
use App\Models\Commodity;
use App\Models\ContainerLoad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ProductDetailController extends Controller
{
    public function index()
    {
        $productDetails = ProductDetail::with(['product', 'hsCode', 'shipmentTitle', 'commodity', 'containerLoad'])
            ->orderBy('id')
            ->get();
        return response()->json(['data' => $productDetails]);
    }

    public function getProducts()
    {
        $products = Product::select('id', 'global_code', 'product_title')
            ->whereNotNull('global_code')
            ->orderBy('global_code')
            ->get();
        return response()->json(['data' => $products]);
    }

    public function getLookups()
    {
        $hscodes = Hscode::select('id', 'hscode')->orderBy('hscode')->get();
        $titles = Title::select('id', 'name')->orderBy('name')->get();
        $commodities = Commodity::select('id', 'name')->orderBy('name')->get();
        $containerLoads = ContainerLoad::select('id', 'name')->orderBy('name')->get();

        return response()->json([
            'hscodes' => $hscodes,
            'titles' => $titles,
            'commodities' => $commodities,
            'container_loads' => $containerLoads,
        ]);
    }

    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'product_details' => 'required|array',
            'product_details.*.product_id' => 'required|exists:products,id',
            'product_details.*.pcs_cases' => 'nullable|string|max:255',
            'product_details.*.cases_pal' => 'nullable|string|max:255',
            'product_details.*.cases_lay' => 'nullable|string|max:255',
            'product_details.*.container_load' => 'nullable|string|max:255',
            'product_details.*.cases_20ft_container' => 'nullable|string|max:255',
            'product_details.*.cases_40ft_container' => 'nullable|string|max:255',
            'product_details.*.total_shelf_life' => 'nullable|string|max:255',
            'product_details.*.gross_weight_cs_kg' => 'nullable|numeric|min:0',
            'product_details.*.net_weight_cs_kg' => 'nullable|numeric|min:0',
            'product_details.*.cbm' => 'nullable|numeric|min:0',
            'product_details.*.hs_code' => 'nullable|string',
            'product_details.*.rate' => 'nullable|numeric|min:0',
            'product_details.*.shipment_title' => 'nullable|string',
            'product_details.*.commodity' => 'nullable|string',
        ]);

        $productDetails = $validated['product_details'];
        $errors = [];
        $created = 0;
        $updated = 0;

        DB::beginTransaction();
        try {
            foreach ($productDetails as $index => $detail) {
                $rowNumber = $index + 1;
                $rowErrors = [];

                // Validate and resolve HS Code (set to null if not found, don't error)
                $hsCodeId = null;
                if (!empty($detail['hs_code'])) {
                    $hsCode = Hscode::where('hscode', $detail['hs_code'])->first();
                    if ($hsCode) {
                        $hsCodeId = $hsCode->id;
                    }
                    // If not found, $hsCodeId remains null (no error)
                }

                // Validate and resolve Shipment Title (set to null if not found, don't error)
                $shipmentTitleId = null;
                if (!empty($detail['shipment_title'])) {
                    $title = Title::where('name', $detail['shipment_title'])->first();
                    if ($title) {
                        $shipmentTitleId = $title->id;
                    }
                    // If not found, $shipmentTitleId remains null (no error)
                }

                // Validate and resolve Commodity (set to null if not found, don't error)
                $commodityId = null;
                if (!empty($detail['commodity'])) {
                    $commodity = Commodity::where('name', $detail['commodity'])->first();
                    if ($commodity) {
                        $commodityId = $commodity->id;
                    }
                    // If not found, $commodityId remains null (no error)
                }

                // Validate and resolve Container Load (set to null if not found, don't error)
                $containerLoadId = null;
                if (!empty($detail['container_load'])) {
                    $containerLoad = ContainerLoad::where('name', $detail['container_load'])->first();
                    if ($containerLoad) {
                        $containerLoadId = $containerLoad->id;
                    }
                    // If not found, $containerLoadId remains null (no error)
                }

                if (!empty($rowErrors)) {
                    $errors[$rowNumber] = $rowErrors;
                    continue;
                }

                // Check if product detail exists
                $existing = ProductDetail::where('product_id', $detail['product_id'])->first();

                $data = [
                    'product_id' => $detail['product_id'],
                    'pcs_cases' => $detail['pcs_cases'] ?? null,
                    'cases_pal' => $detail['cases_pal'] ?? null,
                    'cases_lay' => $detail['cases_lay'] ?? null,
                    'container_load_id' => $containerLoadId,
                    'cases_20ft_container' => $detail['cases_20ft_container'] ?? null,
                    'cases_40ft_container' => $detail['cases_40ft_container'] ?? null,
                    'total_shelf_life' => $detail['total_shelf_life'] ?? null,
                    'gross_weight_cs_kg' => $detail['gross_weight_cs_kg'] ?? null,
                    'net_weight_cs_kg' => $detail['net_weight_cs_kg'] ?? null,
                    'cbm' => $detail['cbm'] ?? null,
                    'hs_code_id' => $hsCodeId,
                    'rate' => $detail['rate'] ?? null,
                    'shipment_title_id' => $shipmentTitleId,
                    'commodity_id' => $commodityId,
                    'created_by' => $request->user()->id,
                    'updated_by' => $request->user()->id,
                ];

                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    ProductDetail::create($data);
                    $created++;
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                $errorRows = implode(', ', array_keys($errors));
                $errorMessages = implode('; ', array_merge(...array_values($errors)));
                return response()->json([
                    'success' => false,
                    'message' => "Validation errors found in rows: {$errorRows}. {$errorMessages}",
                    'errors' => $errors,
                ], 422);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'created' => $created,
                'updated' => $updated,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to save product details: ' . $e->getMessage(),
            ], 500);
        }
    }
}

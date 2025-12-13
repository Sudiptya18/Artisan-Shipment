<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCommodityRequest;
use App\Models\Commodity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CommodityController extends Controller
{
    public function index()
    {
        $commodities = Commodity::orderBy('name')->get();
        return response()->json(['data' => $commodities]);
    }

    public function store(StoreCommodityRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $commodity = DB::transaction(function () use ($data) {
            return Commodity::create($data);
        });

        activity()
            ->performedOn($commodity)
            ->withProperties(['page' => 'commodities'])
            ->log("Commodity created: {$commodity->name}");

        return response()->json(['data' => $commodity], 201);
    }

    public function show(Commodity $commodity)
    {
        return response()->json(['data' => $commodity]);
    }

    public function update(StoreCommodityRequest $request, Commodity $commodity)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $oldValues = $commodity->toArray();

        DB::transaction(function () use ($commodity, $data) {
            $commodity->update($data);
        });

        activity()
            ->performedOn($commodity)
            ->withProperties([
                'page' => 'commodities',
                'old' => $oldValues,
                'attributes' => $commodity->fresh()->toArray(),
            ])
            ->log("Commodity updated: {$commodity->name}");

        return response()->json(['data' => $commodity->fresh()]);
    }

    public function destroy(Commodity $commodity)
    {
        $commodityName = $commodity->name;
        $commodityId = $commodity->id;
        $userId = request()->user()->id;
        
        DB::transaction(function () use ($commodity, $userId) {
            $commodity->deleted_by = $userId;
            $commodity->save();
            $commodity->delete();
        });

        activity()
            ->withProperties([
                'page' => 'commodities',
                'deleted_id' => $commodityId,
            ])
            ->log("Commodity deleted: {$commodityName}");

        return response()->noContent();
    }
}

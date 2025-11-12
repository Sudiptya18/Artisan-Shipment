<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOriginRequest;
use App\Models\Origin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OriginController extends Controller
{
    public function index()
    {
        $origins = Origin::orderBy('origin_name')->get();
        return response()->json(['data' => $origins]);
    }

    public function store(StoreOriginRequest $request)
    {
        $data = $request->validated();

        $origin = DB::transaction(function () use ($data) {
            return Origin::create($data);
        });

        activity()
            ->performedOn($origin)
            ->withProperties(['page' => 'origins'])
            ->log("Origin created: {$origin->origin_name}");

        return response()->json(['data' => $origin], 201);
    }

    public function show(Origin $origin)
    {
        return response()->json(['data' => $origin]);
    }

    public function update(StoreOriginRequest $request, Origin $origin)
    {
        $data = $request->validated();
        $oldValues = $origin->toArray();

        DB::transaction(function () use ($origin, $data) {
            $origin->update($data);
        });

        activity()
            ->performedOn($origin)
            ->withProperties([
                'page' => 'origins',
                'old' => $oldValues,
                'attributes' => $origin->fresh()->toArray(),
            ])
            ->log("Origin updated: {$origin->origin_name}");

        return response()->json(['data' => $origin->fresh()]);
    }

    public function destroy(Origin $origin)
    {
        $originName = $origin->origin_name;
        $originId = $origin->id;
        
        DB::transaction(function () use ($origin) {
            $origin->delete();
        });

        activity()
            ->withProperties([
                'page' => 'origins',
                'deleted_id' => $originId,
            ])
            ->log("Origin deleted: {$originName}");

        return response()->noContent();
    }
}

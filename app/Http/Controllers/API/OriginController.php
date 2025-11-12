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

        return response()->json(['data' => $origin], 201);
    }

    public function show(Origin $origin)
    {
        return response()->json(['data' => $origin]);
    }

    public function update(StoreOriginRequest $request, Origin $origin)
    {
        $data = $request->validated();

        DB::transaction(function () use ($origin, $data) {
            $origin->update($data);
        });

        return response()->json(['data' => $origin->fresh()]);
    }

    public function destroy(Origin $origin)
    {
        DB::transaction(function () use ($origin) {
            $origin->delete();
        });

        return response()->noContent();
    }
}

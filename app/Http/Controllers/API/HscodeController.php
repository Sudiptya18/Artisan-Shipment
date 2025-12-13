<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHscodeRequest;
use App\Models\Hscode;
use Illuminate\Support\Facades\DB;

class HscodeController extends Controller
{
    public function index()
    {
        $hscodes = Hscode::orderBy('hscode')->get();
        return response()->json(['data' => $hscodes]);
    }

    public function store(StoreHscodeRequest $request)
    {
        $data = $request->validated();

        $hscode = DB::transaction(function () use ($data) {
            return Hscode::create($data);
        });

        activity()
            ->performedOn($hscode)
            ->withProperties(['page' => 'hscodes'])
            ->log("HS Code created: {$hscode->hscode}");

        return response()->json(['data' => $hscode], 201);
    }

    public function show(Hscode $hscode)
    {
        return response()->json(['data' => $hscode]);
    }

    public function update(StoreHscodeRequest $request, Hscode $hscode)
    {
        $data = $request->validated();
        $oldValues = $hscode->toArray();

        DB::transaction(function () use ($hscode, $data) {
            $hscode->update($data);
        });

        activity()
            ->performedOn($hscode)
            ->withProperties([
                'page' => 'hscodes',
                'old' => $oldValues,
                'attributes' => $hscode->fresh()->toArray(),
            ])
            ->log("HS Code updated: {$hscode->hscode}");

        return response()->json(['data' => $hscode->fresh()]);
    }

    public function destroy(Hscode $hscode)
    {
        $hscodeValue = $hscode->hscode;
        $hscodeId = $hscode->id;
        
        DB::transaction(function () use ($hscode) {
            $hscode->delete();
        });

        activity()
            ->withProperties([
                'page' => 'hscodes',
                'deleted_id' => $hscodeId,
            ])
            ->log("HS Code deleted: {$hscodeValue}");

        return response()->noContent();
    }
}


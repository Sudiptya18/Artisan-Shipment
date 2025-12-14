<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContainerLoadRequest;
use App\Models\ContainerLoad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContainerLoadController extends Controller
{
    public function index()
    {
        $containerLoads = ContainerLoad::orderBy('name')->get();
        return response()->json(['data' => $containerLoads]);
    }

    public function store(StoreContainerLoadRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $containerLoad = DB::transaction(function () use ($data) {
            return ContainerLoad::create($data);
        });

        activity()
            ->performedOn($containerLoad)
            ->withProperties(['page' => 'container-loads'])
            ->log("Container Load created: {$containerLoad->name}");

        return response()->json(['data' => $containerLoad], 201);
    }

    public function show(ContainerLoad $containerLoad)
    {
        return response()->json(['data' => $containerLoad]);
    }

    public function update(StoreContainerLoadRequest $request, ContainerLoad $containerLoad)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $oldValues = $containerLoad->toArray();

        DB::transaction(function () use ($containerLoad, $data) {
            $containerLoad->update($data);
        });

        activity()
            ->performedOn($containerLoad)
            ->withProperties([
                'page' => 'container-loads',
                'old' => $oldValues,
                'attributes' => $containerLoad->fresh()->toArray(),
            ])
            ->log("Container Load updated: {$containerLoad->name}");

        return response()->json(['data' => $containerLoad->fresh()]);
    }

    public function destroy(ContainerLoad $containerLoad)
    {
        $containerLoadName = $containerLoad->name;
        $containerLoadId = $containerLoad->id;
        $userId = request()->user()->id;
        
        DB::transaction(function () use ($containerLoad, $userId) {
            $containerLoad->deleted_by = $userId;
            $containerLoad->save();
            $containerLoad->delete();
        });

        activity()
            ->withProperties([
                'page' => 'container-loads',
                'deleted_id' => $containerLoadId,
            ])
            ->log("Container Load deleted: {$containerLoadName}");

        return response()->noContent();
    }
}

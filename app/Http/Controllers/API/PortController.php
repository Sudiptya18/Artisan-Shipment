<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePortRequest;
use App\Models\Port;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PortController extends Controller
{
    public function index()
    {
        $ports = Port::orderBy('name')->get();
        return response()->json(['data' => $ports]);
    }

    public function store(StorePortRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $port = DB::transaction(function () use ($data) {
            return Port::create($data);
        });

        activity()
            ->performedOn($port)
            ->withProperties(['page' => 'ports'])
            ->log("Port created: {$port->name}");

        return response()->json(['data' => $port], 201);
    }

    public function show(Port $port)
    {
        return response()->json(['data' => $port]);
    }

    public function update(StorePortRequest $request, Port $port)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $oldValues = $port->toArray();

        DB::transaction(function () use ($port, $data) {
            $port->update($data);
        });

        activity()
            ->performedOn($port)
            ->withProperties([
                'page' => 'ports',
                'old' => $oldValues,
                'attributes' => $port->fresh()->toArray(),
            ])
            ->log("Port updated: {$port->name}");

        return response()->json(['data' => $port->fresh()]);
    }

    public function destroy(Port $port)
    {
        $portName = $port->name;
        $portId = $port->id;
        $userId = request()->user()->id;
        
        DB::transaction(function () use ($port, $userId) {
            $port->deleted_by = $userId;
            $port->save();
            $port->delete();
        });

        activity()
            ->withProperties([
                'page' => 'ports',
                'deleted_id' => $portId,
            ])
            ->log("Port deleted: {$portName}");

        return response()->noContent();
    }
}

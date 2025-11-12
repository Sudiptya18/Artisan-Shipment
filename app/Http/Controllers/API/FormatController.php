<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreFormatRequest;
use App\Models\Format;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FormatController extends Controller
{
    public function index()
    {
        $formats = Format::orderBy('format_name')->get();
        return response()->json(['data' => $formats]);
    }

    public function store(StoreFormatRequest $request)
    {
        $data = $request->validated();

        $format = DB::transaction(function () use ($data) {
            return Format::create($data);
        });

        return response()->json(['data' => $format], 201);
    }

    public function show(Format $format)
    {
        return response()->json(['data' => $format]);
    }

    public function update(StoreFormatRequest $request, Format $format)
    {
        $data = $request->validated();

        DB::transaction(function () use ($format, $data) {
            $format->update($data);
        });

        return response()->json(['data' => $format->fresh()]);
    }

    public function destroy(Format $format)
    {
        DB::transaction(function () use ($format) {
            $format->delete();
        });

        return response()->noContent();
    }
}

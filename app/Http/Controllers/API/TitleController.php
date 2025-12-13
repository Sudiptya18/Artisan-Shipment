<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTitleRequest;
use App\Models\Title;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TitleController extends Controller
{
    public function index()
    {
        $titles = Title::orderBy('name')->get();
        return response()->json(['data' => $titles]);
    }

    public function store(StoreTitleRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $title = DB::transaction(function () use ($data) {
            return Title::create($data);
        });

        activity()
            ->performedOn($title)
            ->withProperties(['page' => 'titles'])
            ->log("Title created: {$title->name}");

        return response()->json(['data' => $title], 201);
    }

    public function show(Title $title)
    {
        return response()->json(['data' => $title]);
    }

    public function update(StoreTitleRequest $request, Title $title)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $oldValues = $title->toArray();

        DB::transaction(function () use ($title, $data) {
            $title->update($data);
        });

        activity()
            ->performedOn($title)
            ->withProperties([
                'page' => 'titles',
                'old' => $oldValues,
                'attributes' => $title->fresh()->toArray(),
            ])
            ->log("Title updated: {$title->name}");

        return response()->json(['data' => $title->fresh()]);
    }

    public function destroy(Title $title)
    {
        $titleName = $title->name;
        $titleId = $title->id;
        $userId = request()->user()->id;
        
        DB::transaction(function () use ($title, $userId) {
            $title->deleted_by = $userId;
            $title->save();
            $title->delete();
        });

        activity()
            ->withProperties([
                'page' => 'titles',
                'deleted_id' => $titleId,
            ])
            ->log("Title deleted: {$titleName}");

        return response()->noContent();
    }
}

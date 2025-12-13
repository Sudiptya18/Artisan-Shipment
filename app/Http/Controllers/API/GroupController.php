<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGroupRequest;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    public function index()
    {
        $groups = Group::orderBy('name')->get();
        return response()->json(['data' => $groups]);
    }

    public function store(StoreGroupRequest $request)
    {
        $data = $request->validated();
        $data['created_by'] = $request->user()->id;

        $group = DB::transaction(function () use ($data) {
            return Group::create($data);
        });

        activity()
            ->performedOn($group)
            ->withProperties(['page' => 'groups'])
            ->log("Group created: {$group->name}");

        return response()->json(['data' => $group], 201);
    }

    public function show(Group $group)
    {
        return response()->json(['data' => $group]);
    }

    public function update(StoreGroupRequest $request, Group $group)
    {
        $data = $request->validated();
        $data['updated_by'] = $request->user()->id;
        $oldValues = $group->toArray();

        DB::transaction(function () use ($group, $data) {
            $group->update($data);
        });

        activity()
            ->performedOn($group)
            ->withProperties([
                'page' => 'groups',
                'old' => $oldValues,
                'attributes' => $group->fresh()->toArray(),
            ])
            ->log("Group updated: {$group->name}");

        return response()->json(['data' => $group->fresh()]);
    }

    public function destroy(Group $group)
    {
        $groupName = $group->name;
        $groupId = $group->id;
        $userId = request()->user()->id;
        
        DB::transaction(function () use ($group, $userId) {
            $group->deleted_by = $userId;
            $group->save();
            $group->delete();
        });

        activity()
            ->withProperties([
                'page' => 'groups',
                'deleted_id' => $groupId,
            ])
            ->log("Group deleted: {$groupName}");

        return response()->noContent();
    }
}

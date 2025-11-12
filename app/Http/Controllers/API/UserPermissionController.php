<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Navigation;
use App\Models\Role;
use App\Models\RolesPolicy;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class UserPermissionController extends Controller
{
    /**
     * Get all users with their roles and activity counts.
     */
    public function getUsers()
    {
        $users = User::with('role')
            ->withCount('activityLogs')
            ->get();
        return response()->json(['data' => $users]);
    }

    /**
     * Get all roles.
     */
    public function getRoles()
    {
        $roles = Role::all();
        return response()->json(['data' => $roles]);
    }

    /**
     * Get all navigations (for permission assignment).
     */
    public function getNavigations()
    {
        $navigations = Navigation::whereNull('parent_id')
            ->with(['children' => function ($query) {
                $query->orderBy('order_by')->orderBy('title');
            }])
            ->orderBy('order_by')
            ->orderBy('title')
            ->get();

        return response()->json(['data' => $navigations]);
    }

    /**
     * Get permissions for a specific user.
     */
    public function getUserPermissions($userId)
    {
        $permissions = RolesPolicy::where('user_id', $userId)
            ->with(['navigation', 'role'])
            ->get();

        return response()->json(['data' => $permissions]);
    }

    /**
     * Set permissions for a user.
     */
    public function setUserPermissions(Request $request)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'role_id' => ['required', 'exists:roles,id'],
            'navigation_ids' => ['required', 'array'],
            'navigation_ids.*' => ['exists:navigations,id'],
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Delete existing permissions for this user and role combination
                RolesPolicy::where('user_id', $validated['user_id'])
                    ->where('role_id', $validated['role_id'])
                    ->delete();

                // Create new permissions
                foreach ($validated['navigation_ids'] as $navigationId) {
                    RolesPolicy::create([
                        'user_id' => $validated['user_id'],
                        'role_id' => $validated['role_id'],
                        'navigation_id' => $navigationId,
                    ]);
                }

                // Update user's role
                $user = User::find($validated['user_id']);
                $user->update(['role_id' => $validated['role_id']]);
                
                // Log activity
                activity()
                    ->performedOn($user)
                    ->withProperties([
                        'page' => 'user-page-permission',
                        'role_id' => $validated['role_id'],
                        'navigation_ids' => $validated['navigation_ids'],
                    ])
                    ->log("Permissions set for user: {$user->name} ({$user->username})");
            });

            return response()->json([
                'success' => true,
                'message' => 'Permissions set successfully!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to set permissions: ' . $e->getMessage(),
            ], 500);
        }
    }
}


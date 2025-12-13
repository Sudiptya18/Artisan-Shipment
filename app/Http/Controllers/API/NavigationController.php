<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NavigationResource;
use App\Models\Navigation;
use App\Models\Role;
use App\Models\RolesPolicy;
use App\Models\User;
use Illuminate\Http\Request;

class NavigationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // If no user, return empty
        if (!$user) {
            return NavigationResource::collection(collect());
        }

        // Auto-grant permissions to Super Admin users for any missing navigations FIRST
        // This must happen before getting allowed navigation IDs
        $this->syncSuperAdminPermissions($user);

        // Get user's allowed navigation IDs (after syncing)
        $allowedNavigationIds = $this->getUserAllowedNavigations($user->id);

        // If user has no permissions, don't show dashboard - they should see contactadministrator page
        if (empty($allowedNavigationIds)) {
            return NavigationResource::collection(collect());
        }

        $navigations = Navigation::query()
            ->with([
                'children' => function ($query) use ($allowedNavigationIds) {
                    $query->visible()
                        ->whereIn('id', $allowedNavigationIds);
                },
            ])
            ->visible()
            ->whereNull('parent_id')
            ->where(function ($query) use ($allowedNavigationIds) {
                $query->whereIn('id', $allowedNavigationIds)
                    ->orWhereHas('children', function ($q) use ($allowedNavigationIds) {
                        $q->whereIn('id', $allowedNavigationIds);
                    });
            })
            ->ordered()
            ->get();

        return NavigationResource::collection($navigations);
    }

    /**
     * Get navigation IDs that the user has permission to access.
     */
    private function getUserAllowedNavigations($userId): array
    {
        return RolesPolicy::where('user_id', $userId)
            ->pluck('navigation_id')
            ->toArray();
    }

    /**
     * Check if user has permission to access a route.
     */
    public function checkRoutePermission(Request $request, $routeName)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json(['allowed' => false], 403);
        }

        // Dashboard is always allowed
        if ($routeName === 'dashboard') {
            return response()->json(['allowed' => true]);
        }

        // Get navigation by route name
        $navigation = Navigation::where('route', $routeName)->first();

        if (!$navigation) {
            // If route not found in navigation, deny access
            return response()->json(['allowed' => false], 403);
        }

        // Check if user has permission for this navigation
        $hasPermission = RolesPolicy::where('user_id', $user->id)
            ->where('navigation_id', $navigation->id)
            ->exists();

        return response()->json(['allowed' => $hasPermission]);
    }

    /**
     * Sync permissions for Super Admin users - grant access to all navigations.
     */
    private function syncSuperAdminPermissions(User $user): void
    {
        $superAdminRole = Role::where('role_name', 'Super Admin')->first();
        
        if (!$superAdminRole || $user->role_id !== $superAdminRole->id) {
            return;
        }

        // Get ALL navigations (including disabled/hidden ones, as they might be enabled later)
        $allNavigations = Navigation::all();
        
        if ($allNavigations->isEmpty()) {
            return;
        }

        // Get user's current permissions
        $userPermissionIds = RolesPolicy::where('user_id', $user->id)
            ->pluck('navigation_id')
            ->toArray();

        // Get navigation IDs that need permissions
        $missingNavigationIds = $allNavigations->pluck('id')
            ->diff($userPermissionIds)
            ->toArray();

        // Grant permissions for any missing navigations in batch
        if (!empty($missingNavigationIds)) {
            $permissions = [];
            foreach ($missingNavigationIds as $navigationId) {
                $permissions[] = [
                    'user_id' => $user->id,
                    'role_id' => $superAdminRole->id,
                    'navigation_id' => $navigationId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            
            // Insert in batch for better performance
            try {
                RolesPolicy::insert($permissions);
            } catch (\Exception $e) {
                // If batch insert fails (e.g., duplicate key), insert one by one
                foreach ($permissions as $permission) {
                    try {
                        RolesPolicy::create($permission);
                    } catch (\Exception $ex) {
                        // Skip if already exists
                        continue;
                    }
                }
            }
        }
    }
}

<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\NavigationResource;
use App\Models\Navigation;
use App\Models\RolesPolicy;
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

        // Get user's allowed navigation IDs
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
}

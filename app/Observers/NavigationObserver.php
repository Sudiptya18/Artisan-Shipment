<?php

namespace App\Observers;

use App\Models\Navigation;
use App\Models\Role;
use App\Models\RolesPolicy;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class NavigationObserver
{
    /**
     * Handle the Navigation "created" event.
     */
    public function created(Navigation $navigation): void
    {
        // Automatically grant permissions to all Super Admin users for new navigation
        $this->grantPermissionsToSuperAdmin($navigation);
    }

    /**
     * Grant permissions to all Super Admin users for a navigation.
     */
    private function grantPermissionsToSuperAdmin(Navigation $navigation): void
    {
        $superAdminRole = Role::where('role_name', 'Super Admin')->first();
        
        if (!$superAdminRole) {
            return;
        }

        $superAdminUsers = User::where('role_id', $superAdminRole->id)->get();

        foreach ($superAdminUsers as $user) {
            // Check if permission already exists
            $exists = RolesPolicy::where('user_id', $user->id)
                ->where('role_id', $superAdminRole->id)
                ->where('navigation_id', $navigation->id)
                ->exists();

            if (!$exists) {
                RolesPolicy::create([
                    'user_id' => $user->id,
                    'role_id' => $superAdminRole->id,
                    'navigation_id' => $navigation->id,
                ]);
            }
        }
    }
}

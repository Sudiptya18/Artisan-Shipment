<?php

namespace App\Console\Commands;

use App\Models\Navigation;
use App\Models\Role;
use App\Models\RolesPolicy;
use App\Models\User;
use Illuminate\Console\Command;

class SyncNavigationPermissions extends Command
{
    protected $signature = 'navigation:sync-permissions {--user-id= : Sync for specific user ID}';
    protected $description = 'Sync navigation permissions for Super Admin users';

    public function handle()
    {
        $superAdminRole = Role::where('role_name', 'Super Admin')->first();

        if (!$superAdminRole) {
            $this->error('Super Admin role not found!');
            return 1;
        }

        $userId = $this->option('user-id');
        
        if ($userId) {
            $users = User::where('id', $userId)->where('role_id', $superAdminRole->id)->get();
        } else {
            $users = User::where('role_id', $superAdminRole->id)->get();
        }

        if ($users->isEmpty()) {
            $this->warn('No Super Admin users found!');
            return 1;
        }

        $allNavigations = Navigation::all();
        $this->info("Found {$allNavigations->count()} navigation(s) and {$users->count()} Super Admin user(s)");

        $totalGranted = 0;
        foreach ($users as $user) {
            $userPermissionIds = RolesPolicy::where('user_id', $user->id)
                ->pluck('navigation_id')
                ->toArray();

            $granted = 0;
            foreach ($allNavigations as $navigation) {
                if (!in_array($navigation->id, $userPermissionIds)) {
                    RolesPolicy::create([
                        'user_id' => $user->id,
                        'role_id' => $superAdminRole->id,
                        'navigation_id' => $navigation->id,
                    ]);
                    $granted++;
                }
            }
            
            $this->info("✓ {$user->name} ({$user->username}): {$granted} new permission(s) granted");
            $totalGranted += $granted;
        }

        $this->info("\n✓ Total: {$totalGranted} permission(s) granted.");
        return 0;
    }
}

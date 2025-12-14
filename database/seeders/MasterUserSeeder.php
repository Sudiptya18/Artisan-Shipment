<?php

namespace Database\Seeders;

use App\Models\Navigation;
use App\Models\Role;
use App\Models\RolesPolicy;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class MasterUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create Super Admin role
        $superAdminRole = Role::where('role_name', 'Super Admin')->first();
        
        if (!$superAdminRole) {
            $superAdminRole = Role::create(['role_name' => 'Super Admin']);
        }

        // Create or update master user
        $masterUser = User::updateOrCreate(
            ['username' => '21'],
            [
                'name' => 'Master Admin',
                'email' => 'master@artisan.localhost',
                'password' => Hash::make(',wORe0Zr'),
                'is_active' => true,
                'role_id' => $superAdminRole->id,
            ]
        );

        // Get all navigations and assign all permissions to all Super Admin users
        $allNavigations = Navigation::all();
        
        // Get all users with Super Admin role
        $superAdminUsers = User::where('role_id', $superAdminRole->id)->get();
        
        // Delete all existing roles_policies first
        RolesPolicy::query()->delete();
        
        // Reset AUTO_INCREMENT to start from 1
        DB::statement('ALTER TABLE roles_policies AUTO_INCREMENT = 1');
        
        // Assign all navigation permissions to all Super Admin users
        foreach ($superAdminUsers as $user) {
            // Assign all navigation permissions
            foreach ($allNavigations as $navigation) {
                RolesPolicy::create([
                    'user_id' => $user->id,
                    'role_id' => $superAdminRole->id,
                    'navigation_id' => $navigation->id,
                ]);
            }
        }
    }
}

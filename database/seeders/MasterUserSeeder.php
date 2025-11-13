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
        DB::transaction(function () {
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

            // Get all navigations and assign all permissions to master user
            $allNavigations = Navigation::all();
            
            // Delete existing permissions for master user
            RolesPolicy::where('user_id', $masterUser->id)->delete();
            
            // Assign all navigation permissions to master user
            foreach ($allNavigations as $navigation) {
                RolesPolicy::updateOrCreate(
                    [
                        'user_id' => $masterUser->id,
                        'role_id' => $superAdminRole->id,
                        'navigation_id' => $navigation->id,
                    ],
                    [
                        'user_id' => $masterUser->id,
                        'role_id' => $superAdminRole->id,
                        'navigation_id' => $navigation->id,
                    ]
                );
            }
        });
    }
}

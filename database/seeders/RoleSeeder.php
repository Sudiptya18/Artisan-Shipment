<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::transaction(function () {
            $roles = ['Super Admin', 'Admin', 'Moderator'];
            
            foreach ($roles as $roleName) {
                Role::updateOrCreate(
                    ['role_name' => $roleName],
                    ['role_name' => $roleName]
                );
            }
        });
    }
}


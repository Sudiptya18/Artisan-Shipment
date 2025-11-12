<?php

namespace Database\Seeders;

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
            User::updateOrCreate(
                ['username' => '1'],
                [
                    'name' => 'Master Admin',
                    'email' => 'master@artisan.localhost',
                    'password' => Hash::make('123'),
                    'is_active' => true,
                ]
            );
        });
    }
}

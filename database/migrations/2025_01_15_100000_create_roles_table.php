<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('roles')) {
            Schema::create('roles', function (Blueprint $table) {
                $table->id();
                $table->string('role_name', 100)->unique();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });

            // Seed default roles
            DB::table('roles')->insert([
                ['role_name' => 'Super Admin', 'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Admin', 'created_at' => now(), 'updated_at' => now()],
                ['role_name' => 'Moderator', 'created_at' => now(), 'updated_at' => now()],
            ]);
        } else {
            Schema::table('roles', function (Blueprint $table) {
                if (!Schema::hasColumn('roles', 'role_name')) {
                    $table->string('role_name', 100)->unique()->after('id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};


<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('roles_policies')) {
            Schema::create('roles_policies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')
                    ->constrained('users')
                    ->cascadeOnDelete();
                $table->foreignId('role_id')
                    ->constrained('roles')
                    ->cascadeOnDelete();
                $table->foreignId('navigation_id')
                    ->constrained('navigations')
                    ->cascadeOnDelete();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();

                // Ensure unique combination of user, role, and navigation
                $table->unique(['user_id', 'role_id', 'navigation_id'], 'unique_user_role_navigation');
            });
        } else {
            Schema::table('roles_policies', function (Blueprint $table) {
                if (!Schema::hasColumn('roles_policies', 'user_id')) {
                    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('roles_policies', 'role_id')) {
                    $table->foreignId('role_id')->constrained('roles')->cascadeOnDelete()->after('user_id');
                }
                if (!Schema::hasColumn('roles_policies', 'navigation_id')) {
                    $table->foreignId('navigation_id')->constrained('navigations')->cascadeOnDelete()->after('role_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles_policies');
    }
};


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
        if (!Schema::hasColumn('users', 'name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('name')->after('id');
            });
        }

        // Migrate existing data if first_name and last_name exist
        if (Schema::hasColumn('users', 'first_name') && Schema::hasColumn('users', 'last_name')) {
            DB::statement('UPDATE users SET name = TRIM(CONCAT(COALESCE(first_name, ""), " ", COALESCE(last_name, ""))) WHERE (name IS NULL OR name = "") AND (first_name IS NOT NULL OR last_name IS NOT NULL)');
        }

        if (Schema::hasColumn('users', 'first_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('first_name');
            });
        }

        if (Schema::hasColumn('users', 'last_name')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('last_name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->after('id');
            $table->string('last_name')->nullable()->after('first_name');
        });

        // Migrate data back
        DB::statement('UPDATE users SET first_name = SUBSTRING_INDEX(name, " ", 1), last_name = SUBSTRING_INDEX(name, " ", -1) WHERE first_name IS NULL');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('name');
        });
    }
};

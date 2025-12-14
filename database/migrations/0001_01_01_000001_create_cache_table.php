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
        if (!Schema::hasTable('cache')) {
            Schema::create('cache', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->mediumText('value');
                $table->integer('expiration');
            });
        } else {
            Schema::table('cache', function (Blueprint $table) {
                if (!Schema::hasColumn('cache', 'value')) {
                    $table->mediumText('value')->after('key');
                }
                if (!Schema::hasColumn('cache', 'expiration')) {
                    $table->integer('expiration')->after('value');
                }
            });
        }

        if (!Schema::hasTable('cache_locks')) {
            Schema::create('cache_locks', function (Blueprint $table) {
                $table->string('key')->primary();
                $table->string('owner');
                $table->integer('expiration');
            });
        } else {
            Schema::table('cache_locks', function (Blueprint $table) {
                if (!Schema::hasColumn('cache_locks', 'owner')) {
                    $table->string('owner')->after('key');
                }
                if (!Schema::hasColumn('cache_locks', 'expiration')) {
                    $table->integer('expiration')->after('owner');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache');
        Schema::dropIfExists('cache_locks');
    }
};

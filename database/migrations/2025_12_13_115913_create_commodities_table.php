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
        if (!Schema::hasTable('commodities')) {
            Schema::create('commodities', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();
            });
        } else {
            Schema::table('commodities', function (Blueprint $table) {
                if (!Schema::hasColumn('commodities', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('commodities', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('commodities', 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('commodities', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('commodities', 'deleted_by')) {
                    $table->unsignedBigInteger('deleted_by')->nullable()->after('deleted_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commodities');
    }
};

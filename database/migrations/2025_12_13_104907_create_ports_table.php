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
        if (!Schema::hasTable('ports')) {
            Schema::create('ports', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();
            });
        } else {
            Schema::table('ports', function (Blueprint $table) {
                if (!Schema::hasColumn('ports', 'name')) {
                    $table->string('name')->after('id');
                }
                if (!Schema::hasColumn('ports', 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn('ports', 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('created_by');
                }
                if (!Schema::hasColumn('ports', 'deleted_at')) {
                    $table->softDeletes();
                }
                if (!Schema::hasColumn('ports', 'deleted_by')) {
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
        Schema::dropIfExists('ports');
    }
};

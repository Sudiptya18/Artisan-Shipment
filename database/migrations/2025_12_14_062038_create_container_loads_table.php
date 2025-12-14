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
        if (!Schema::hasTable('container_loads')) {
            Schema::create('container_loads', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
                $table->softDeletes();
                $table->unsignedBigInteger('deleted_by')->nullable();
            });
        }
        // If table exists, skip all column additions - existing columns are fine
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('container_loads');
    }
};

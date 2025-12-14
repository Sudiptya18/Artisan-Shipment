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
        if (!Schema::hasTable('product_details')) {
            Schema::create('product_details', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->unique()->constrained('products')->cascadeOnDelete();
                $table->string('pcs_cases')->nullable();
                $table->string('cases_pal')->nullable();
                $table->string('cases_lay')->nullable();
                $table->string('container_load')->nullable();
                $table->string('cases_20ft_container')->nullable();
                $table->string('cases_40ft_container')->nullable();
                $table->string('total_shelf_life')->nullable();
                $table->decimal('gross_weight_cs_kg', 10, 2)->nullable();
                $table->decimal('net_weight_cs_kg', 10, 2)->nullable();
                $table->decimal('cbm', 10, 2)->nullable();
                $table->foreignId('hs_code_id')->nullable()->constrained('hscodes')->nullOnDelete();
                $table->decimal('rate', 10, 2)->nullable();
                $table->foreignId('shipment_title_id')->nullable()->constrained('titles')->nullOnDelete();
                $table->foreignId('commodity_id')->nullable()->constrained('commodities')->nullOnDelete();
                $table->timestamps();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->unsignedBigInteger('updated_by')->nullable();
            });
        }
        // If table exists, skip all column additions - existing columns are fine
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_details');
    }
};

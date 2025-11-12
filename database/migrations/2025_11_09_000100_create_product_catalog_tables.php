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
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('brand_name');
            $table->string('b_image', 1024)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_name');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::create('formats', function (Blueprint $table) {
            $table->id();
            $table->string('format_name', 128);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::create('origins', function (Blueprint $table) {
            $table->id();
            $table->string('origin_name', 128);
            $table->string('iso_code', 8)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('sku', 128)->unique('idx_sku');
            $table->string('product_title', 512);
            $table->string('global_code', 128)->nullable()->index('idx_global_code');
            $table->text('description')->nullable();
            $table->text('benefits')->nullable();
            $table->string('pack_size', 128)->nullable();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            $table->foreignId('format_id')->nullable()->constrained('formats')->nullOnDelete();
            $table->foreignId('origin_id')->nullable()->constrained('origins')->nullOnDelete();
            $table->boolean('active')->default(true);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });

        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement('CREATE INDEX idx_title ON products(product_title(255))');
        }

        Schema::create('product_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
            $table->string('image_url', 1024);
            $table->string('alt_text', 255)->nullable();
            $table->integer('position')->default(0);
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_images');
        Schema::dropIfExists('products');
        Schema::dropIfExists('origins');
        Schema::dropIfExists('formats');
        Schema::dropIfExists('categories');
        Schema::dropIfExists('brands');
    }
};


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
        if (!Schema::hasTable('brands')) {
            Schema::create('brands', function (Blueprint $table) {
                $table->id();
                $table->string('brand_name');
                $table->string('b_image', 1024)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }
        // If table exists, skip all column additions - existing columns are fine

        if (!Schema::hasTable('categories')) {
            Schema::create('categories', function (Blueprint $table) {
                $table->id();
                $table->string('category_name');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }
        // If table exists, skip all column additions - existing columns are fine

        if (!Schema::hasTable('formats')) {
            Schema::create('formats', function (Blueprint $table) {
                $table->id();
                $table->string('format_name', 128);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }
        // If table exists, skip all column additions - existing columns are fine

        if (!Schema::hasTable('origins')) {
            Schema::create('origins', function (Blueprint $table) {
                $table->id();
                $table->string('origin_name', 128);
                $table->string('iso_code', 8)->nullable();
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }
        // If table exists, skip all column additions - existing columns are fine

        if (!Schema::hasTable('products')) {
            Schema::create('products', function (Blueprint $table) {
                $table->id();
                $table->string('sku', 128); // NOT NULL but not unique (can have empty strings)
                $table->string('product_title', 512)->nullable();
                $table->string('global_code', 128)->unique();
                $table->text('description')->nullable();
                $table->text('benefits')->nullable();
                $table->string('pack_size', 128)->nullable();
                $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
                $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
                $table->foreignId('format_id')->nullable()->constrained('formats')->nullOnDelete();
                $table->foreignId('origin_id')->nullable()->constrained('origins')->nullOnDelete();
                $table->enum('status', [
                    'ACTIVE',
                    'DISCONTINUED-UI',
                    'DISCONTINUED-ARTISAN',
                    'REPLACEMENT',
                    'REPLACEMENT & DISCONTINUED',
                    'NEW CODE',
                    'FUTURE DISCONTINUED',
                    'NEW TENTATIVE',
                    'DISCONTINUED'
                ])->default('ACTIVE');
                $table->boolean('active')->default(true);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });

            if (Schema::getConnection()->getDriverName() === 'mysql') {
                DB::statement('CREATE INDEX IF NOT EXISTS idx_global_code ON products(global_code)');
                DB::statement('CREATE INDEX IF NOT EXISTS idx_title ON products(product_title(255))');
            }
        }
        // If table exists, skip all column additions - existing columns are fine

        if (!Schema::hasTable('product_images')) {
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
        } else {
            Schema::table('product_images', function (Blueprint $table) {
                if (!Schema::hasColumn('product_images', 'product_id')) {
                    $table->foreignId('product_id')->constrained('products')->cascadeOnDelete()->after('id');
                }
                if (!Schema::hasColumn('product_images', 'image_url')) {
                    $table->string('image_url', 1024)->after('product_id');
                }
                if (!Schema::hasColumn('product_images', 'alt_text')) {
                    $table->string('alt_text', 255)->nullable()->after('image_url');
                }
                if (!Schema::hasColumn('product_images', 'position')) {
                    $table->integer('position')->default(0)->after('alt_text');
                }
            });
        }
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


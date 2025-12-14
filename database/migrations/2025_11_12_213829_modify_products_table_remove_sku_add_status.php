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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                // Ensure sku exists and is NOT NULL but not unique (matches SQL dump)
                if (!Schema::hasColumn('products', 'sku')) {
                    $table->string('sku', 128)->after('id');
                }

                // Ensure active column exists
                if (!Schema::hasColumn('products', 'active')) {
                    $table->boolean('active')->default(true)->after('status');
                }

                // Ensure global_code is not null and unique
                if (Schema::hasColumn('products', 'global_code')) {
                    // Update any null values before making it not null
                    DB::statement("UPDATE products SET global_code = CONCAT('GC-', id) WHERE global_code IS NULL");
                }

                // Add status enum column with all values including 'DISCONTINUED'
                if (!Schema::hasColumn('products', 'status')) {
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
                    ])->default('ACTIVE')->after('origin_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Restore SKU column
            if (!Schema::hasColumn('products', 'sku')) {
                $table->string('sku', 128)->nullable()->unique('idx_sku')->after('id');
            }

            // Restore active column
            if (!Schema::hasColumn('products', 'active')) {
                $table->boolean('active')->default(true)->after('origin_id');
            }

            // Make global_code nullable again
            if (Schema::hasColumn('products', 'global_code')) {
                $table->string('global_code', 128)->nullable()->change();
            }

            // Remove status column
            if (Schema::hasColumn('products', 'status')) {
                $table->dropColumn('status');
            }

            // Make product_title required again
            if (Schema::hasColumn('products', 'product_title')) {
                DB::statement('ALTER TABLE products MODIFY product_title VARCHAR(512) NOT NULL');
            }
        });
    }
};

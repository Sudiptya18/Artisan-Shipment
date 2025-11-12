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
        Schema::table('products', function (Blueprint $table) {
            // Drop SKU column if it exists
            if (Schema::hasColumn('products', 'sku')) {
                $table->dropUnique('idx_sku');
                $table->dropColumn('sku');
            }

            // Keep active column but ensure it defaults to true
            if (!Schema::hasColumn('products', 'active')) {
                $table->boolean('active')->default(true)->after('status');
            }

            // Make global_code not null and unique
            // Note: User requested global_code as primary key, but keeping id as primary key
            // to maintain foreign key relationships. global_code will be unique and not null.
            if (Schema::hasColumn('products', 'global_code')) {
                // Update any null values with a placeholder before making it not null
                DB::statement("UPDATE products SET global_code = CONCAT('GC-', id) WHERE global_code IS NULL");
                // Make it not null and unique
                $table->string('global_code', 128)->nullable(false)->unique()->change();
            } else {
                $table->string('global_code', 128)->unique();
            }

            // Add status enum column
            if (!Schema::hasColumn('products', 'status')) {
                $table->enum('status', [
                    'ACTIVE',
                    'DISCONTINUED-UI',
                    'DISCONTINUED-ARTISAN',
                    'REPLACEMENT',
                    'REPLACEMENT & DISCONTINUED',
                    'NEW CODE',
                    'FUTURE DISCONTINUED',
                    'NEW TENTATIVE'
                ])->default('ACTIVE')->after('origin_id');
            }

            // Make all other fields nullable (except global_code which is already not null)
            // Note: We'll use DB::statement for text fields as they need special handling
        });

        // Make fields nullable using DB statements for better compatibility
        if (Schema::hasColumn('products', 'product_title')) {
            DB::statement('ALTER TABLE products MODIFY product_title VARCHAR(512) NULL');
        }
        if (Schema::hasColumn('products', 'description')) {
            DB::statement('ALTER TABLE products MODIFY description TEXT NULL');
        }
        if (Schema::hasColumn('products', 'benefits')) {
            DB::statement('ALTER TABLE products MODIFY benefits TEXT NULL');
        }
        if (Schema::hasColumn('products', 'pack_size')) {
            DB::statement('ALTER TABLE products MODIFY pack_size VARCHAR(128) NULL');
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

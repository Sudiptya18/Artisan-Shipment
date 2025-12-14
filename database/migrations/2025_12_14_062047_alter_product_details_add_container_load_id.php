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
        if (Schema::hasTable('product_details')) {
            Schema::table('product_details', function (Blueprint $table) {
                // Drop old container_load varchar column if it exists
                if (Schema::hasColumn('product_details', 'container_load')) {
                    $table->dropColumn('container_load');
                }
                
                // Add new container_load_id foreign key
                if (!Schema::hasColumn('product_details', 'container_load_id')) {
                    $table->foreignId('container_load_id')
                        ->nullable()
                        ->after('cases_lay')
                        ->constrained('container_loads')
                        ->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('product_details')) {
            Schema::table('product_details', function (Blueprint $table) {
                if (Schema::hasColumn('product_details', 'container_load_id')) {
                    $table->dropForeign(['container_load_id']);
                    $table->dropColumn('container_load_id');
                }
                
                // Restore old container_load column
                if (!Schema::hasColumn('product_details', 'container_load')) {
                    $table->string('container_load')->nullable()->after('cases_lay');
                }
            });
        }
    }
};

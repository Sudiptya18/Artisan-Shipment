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
        if (!Schema::hasTable('navigations')) {
            Schema::create('navigations', function (Blueprint $table) {
                $table->id();
                $table->string('key')->unique();
                $table->string('title', 100);
                $table->string('route', 200)->nullable();
                $table->string('url', 200)->nullable();
                $table->longText('icon')->nullable();
                $table->longText('permissions')->nullable();
                $table->foreignId('parent_id')
                    ->nullable()
                    ->constrained('navigations')
                    ->nullOnDelete();
                $table->unsignedBigInteger('order_by')->default(0)->index();
                $table->boolean('is_enabled')->default(true);
                $table->boolean('is_visible')->default(true);
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        } else {
            Schema::table('navigations', function (Blueprint $table) {
                if (!Schema::hasColumn('navigations', 'key')) {
                    $table->string('key')->unique()->after('id');
                }
                if (!Schema::hasColumn('navigations', 'title')) {
                    $table->string('title', 100)->after('key');
                }
                if (!Schema::hasColumn('navigations', 'route')) {
                    $table->string('route', 200)->nullable()->after('title');
                }
                if (!Schema::hasColumn('navigations', 'url')) {
                    $table->string('url', 200)->nullable()->after('route');
                }
                if (!Schema::hasColumn('navigations', 'icon')) {
                    $table->longText('icon')->nullable()->after('url');
                }
                if (!Schema::hasColumn('navigations', 'permissions')) {
                    $table->longText('permissions')->nullable()->after('icon');
                }
                if (!Schema::hasColumn('navigations', 'parent_id')) {
                    $table->foreignId('parent_id')->nullable()->constrained('navigations')->nullOnDelete()->after('permissions');
                }
                if (!Schema::hasColumn('navigations', 'order_by')) {
                    $table->unsignedBigInteger('order_by')->default(0)->index()->after('parent_id');
                }
                if (!Schema::hasColumn('navigations', 'is_enabled')) {
                    $table->boolean('is_enabled')->default(true)->after('order_by');
                }
                if (!Schema::hasColumn('navigations', 'is_visible')) {
                    $table->boolean('is_visible')->default(true)->after('is_enabled');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigations');
    }
};

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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('navigations');
    }
};

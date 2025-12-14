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
        if (!Schema::hasTable('hscodes')) {
            Schema::create('hscodes', function (Blueprint $table) {
                $table->id();
                $table->string('hscode', 50)->unique();
                $table->text('description')->nullable();
                $table->decimal('cd', 10, 2)->nullable()->comment('Custom Duty');
                $table->decimal('rd', 10, 2)->nullable()->comment('Regulatory Duty');
                $table->decimal('sd', 10, 2)->nullable()->comment('Supplementary Duty');
                $table->decimal('vat', 10, 2)->nullable()->comment('VAT');
                $table->decimal('ait', 10, 2)->nullable()->comment('Advance Income Tax');
                $table->decimal('at', 10, 2)->nullable()->comment('Advance Tax');
                $table->decimal('exd', 10, 2)->nullable()->comment('Export Development Duty');
                $table->decimal('tti', 10, 2)->nullable()->comment('Turnover Tax on Import');
                $table->decimal('min_ass_value', 10, 2)->nullable()->comment('Minimum Assessed Value');
                $table->timestamp('created_at')->useCurrent();
                $table->timestamp('updated_at')->nullable()->useCurrentOnUpdate();
            });
        }
        // If table exists, skip all column additions - existing columns are fine
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hscodes');
    }
};


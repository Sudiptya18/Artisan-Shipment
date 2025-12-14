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
        if (!Schema::hasTable('personal_access_tokens')) {
            Schema::create('personal_access_tokens', function (Blueprint $table) {
                $table->id();
                $table->morphs('tokenable');
                $table->text('name');
                $table->string('token', 64)->unique();
                $table->text('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable()->index();
                $table->timestamps();
            });
        } else {
            Schema::table('personal_access_tokens', function (Blueprint $table) {
                if (!Schema::hasColumn('personal_access_tokens', 'tokenable_type')) {
                    $table->string('tokenable_type')->after('id');
                }
                if (!Schema::hasColumn('personal_access_tokens', 'tokenable_id')) {
                    $table->unsignedBigInteger('tokenable_id')->after('tokenable_type');
                }
                if (!Schema::hasColumn('personal_access_tokens', 'name')) {
                    $table->text('name')->after('tokenable_id');
                }
                if (!Schema::hasColumn('personal_access_tokens', 'token')) {
                    $table->string('token', 64)->unique()->after('name');
                }
                if (!Schema::hasColumn('personal_access_tokens', 'abilities')) {
                    $table->text('abilities')->nullable()->after('token');
                }
                if (!Schema::hasColumn('personal_access_tokens', 'last_used_at')) {
                    $table->timestamp('last_used_at')->nullable()->after('abilities');
                }
                if (!Schema::hasColumn('personal_access_tokens', 'expires_at')) {
                    $table->timestamp('expires_at')->nullable()->index()->after('last_used_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};

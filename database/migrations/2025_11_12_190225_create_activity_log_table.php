<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        $connection = Schema::connection(config('activitylog.database_connection'));
        $tableName = config('activitylog.table_name');
        
        if (!$connection->hasTable($tableName)) {
            $connection->create($tableName, function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('log_name')->nullable();
                $table->text('description');
                $table->nullableMorphs('subject', 'subject');
                $table->nullableMorphs('causer', 'causer');
                $table->json('properties')->nullable();
                $table->timestamps();
                $table->index('log_name');
            });
        } else {
            $connection->table($tableName, function (Blueprint $table) use ($tableName, $connection) {
                if (!$connection->hasColumn($tableName, 'log_name')) {
                    $table->string('log_name')->nullable()->after('id');
                }
                if (!$connection->hasColumn($tableName, 'description')) {
                    $table->text('description')->after('log_name');
                }
                if (!$connection->hasColumn($tableName, 'subject_type')) {
                    $table->string('subject_type')->nullable()->after('description');
                }
                if (!$connection->hasColumn($tableName, 'subject_id')) {
                    $table->unsignedBigInteger('subject_id')->nullable()->after('subject_type');
                }
                if (!$connection->hasColumn($tableName, 'causer_type')) {
                    $table->string('causer_type')->nullable()->after('subject_id');
                }
                if (!$connection->hasColumn($tableName, 'causer_id')) {
                    $table->unsignedBigInteger('causer_id')->nullable()->after('causer_type');
                }
                if (!$connection->hasColumn($tableName, 'properties')) {
                    $table->json('properties')->nullable()->after('causer_id');
                }
            });
        }
    }

    public function down()
    {
        Schema::connection(config('activitylog.database_connection'))->dropIfExists(config('activitylog.table_name'));
    }
}

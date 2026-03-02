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
        $tables = ['billers', 'brands', 'categories', 'collections', 'employees', 'products', 'suppliers', 'users'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'image')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('image', 'image_path');
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = ['billers', 'brands', 'categories', 'collections', 'employees', 'products', 'suppliers', 'users'];

        foreach ($tables as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'image_path')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->renameColumn('image_path', 'image');
                });
            }
        }
    }
};

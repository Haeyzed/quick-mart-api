<?php

declare(strict_types=1);

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
        $tableNames = config('permission.table_names');
        if (empty($tableNames['permissions'])) {
            return;
        }

        Schema::table($tableNames['permissions'], static function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('guard_name');
            $table->string('module')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tableNames = config('permission.table_names');
        if (empty($tableNames['permissions'])) {
            return;
        }

        Schema::table($tableNames['permissions'], static function (Blueprint $table) {
            $table->dropColumn(['is_active', 'module']);
        });
    }
};

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
        Schema::table('departments', function (Blueprint $table) {
            $table->foreignId('parent_id')->nullable()->after('is_active')->constrained('departments')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('manager_id')->nullable()->after('parent_id')->constrained('employees')->nullOnDelete()->cascadeOnUpdate();

            $table->index('parent_id');
            $table->index('manager_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropForeign(['manager_id']);
        });
    }
};

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
        Schema::table('shifts', function (Blueprint $table) {
            $table->unsignedInteger('break_duration')->default(0)->after('total_hours')->comment('Break duration in minutes');
            $table->boolean('is_rotational')->default(false)->after('break_duration');
            $table->boolean('overtime_allowed')->default(true)->after('is_rotational');

            $table->index('is_rotational');
            $table->index('overtime_allowed');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('shifts', function (Blueprint $table) {
            $table->dropColumn(['break_duration', 'is_rotational', 'overtime_allowed']);
        });
    }
};

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
        Schema::table('attendances', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('user_id')->constrained('shifts')->nullOnDelete()->cascadeOnUpdate();
            $table->unsignedInteger('late_minutes')->default(0)->after('checkout');
            $table->unsignedInteger('early_exit_minutes')->default(0)->after('late_minutes');
            $table->decimal('worked_hours', 5, 2)->nullable()->after('early_exit_minutes');
            $table->decimal('overtime_minutes', 6, 2)->default(0)->after('worked_hours');
            $table->string('checkin_source')->nullable()->after('overtime_minutes');
            $table->decimal('latitude', 10, 8)->nullable()->after('checkin_source');
            $table->decimal('longitude', 11, 8)->nullable()->after('latitude');

            $table->index('shift_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('attendances', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropColumn([
                'shift_id',
                'late_minutes',
                'early_exit_minutes',
                'worked_hours',
                'overtime_minutes',
                'checkin_source',
                'latitude',
                'longitude',
            ]);
        });
    }
};

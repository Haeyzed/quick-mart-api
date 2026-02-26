<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {

            $table->foreignId('department_id')
                ->nullable()
                ->after('user_id')
                ->constrained('departments')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('designation_id')
                ->nullable()
                ->after('department_id')
                ->constrained('designations')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreignId('shift_id')
                ->nullable()
                ->after('designation_id')
                ->constrained('shifts')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('department_id');
            $table->index('designation_id');
            $table->index('shift_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['shift_id']);

            $table->dropColumn([
                'department_id',
                'designation_id',
                'shift_id'
            ]);
        });
    }
};

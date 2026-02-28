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
        Schema::table('employees', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->foreignId('employment_type_id')->nullable()->after('shift_id')->constrained('employment_types')->nullOnDelete()->cascadeOnUpdate();
            $table->date('joining_date')->nullable()->after('employment_type_id');
            $table->date('confirmation_date')->nullable()->after('joining_date');
            $table->date('probation_end_date')->nullable()->after('confirmation_date');
            $table->foreignId('reporting_manager_id')->nullable()->after('probation_end_date')->constrained('employees')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('warehouse_id')->nullable()->after('reporting_manager_id')->constrained('warehouses')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('work_location_id')->nullable()->after('warehouse_id')->constrained('work_locations')->nullOnDelete()->cascadeOnUpdate();
            $table->foreignId('salary_structure_id')->nullable()->after('work_location_id')->constrained('salary_structures')->nullOnDelete()->cascadeOnUpdate();
            $table->string('employment_status')->default('active')->after('salary_structure_id');

            $table->index('employee_code');
            $table->index('employment_type_id');
            $table->index('reporting_manager_id');
            $table->index('warehouse_id');
            $table->index('work_location_id');
            $table->index('salary_structure_id');
            $table->index('employment_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['reporting_manager_id']);
            $table->dropForeign(['employment_type_id']);
            $table->dropForeign(['warehouse_id']);
            $table->dropForeign(['work_location_id']);
            $table->dropForeign(['salary_structure_id']);
        });
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'employee_code',
                'employment_type_id',
                'joining_date',
                'confirmation_date',
                'probation_end_date',
                'reporting_manager_id',
                'warehouse_id',
                'work_location_id',
                'salary_structure_id',
                'employment_status',
            ]);
        });
    }
};

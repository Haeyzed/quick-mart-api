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
        Schema::create('payroll_runs', function (Blueprint $table) {
            $table->id();
            $table->string('month', 7);
            $table->unsignedSmallInteger('year');
            $table->string('status')->default('draft');
            $table->foreignId('generated_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['month', 'year']);
            $table->index('status');
        });

        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_run_id')->constrained('payroll_runs')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->decimal('gross_salary', 12, 2)->default(0);
            $table->decimal('total_deductions', 12, 2)->default(0);
            $table->decimal('net_salary', 12, 2)->default(0);
            $table->string('status')->default('draft');
            $table->timestamps();

            $table->unique(['payroll_run_id', 'employee_id'], 'payroll_entries_run_employee_unique');
            $table->index('status');
        });

        Schema::create('payroll_entry_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_entry_id')->constrained('payroll_entries')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->timestamps();

            $table->unique(['payroll_entry_id', 'salary_component_id'], 'payroll_entry_items_entry_component_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_entry_items');
        Schema::dropIfExists('payroll_entries');
        Schema::dropIfExists('payroll_runs');
    }
};

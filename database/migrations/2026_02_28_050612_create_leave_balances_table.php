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
        Schema::create('leave_balances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('leave_type_id')->constrained('leave_types')->cascadeOnDelete();
            $table->unsignedInteger('year');
            $table->decimal('balance', 6, 2)->default(0);
            $table->decimal('used', 6, 2)->default(0);
            $table->decimal('carried_forward', 6, 2)->default(0);
            $table->timestamps();

            $table->unique(['employee_id', 'leave_type_id', 'year'], 'leave_balances_employee_type_year_unique');
            $table->index('year');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_balances');
    }
};

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
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->text('address')->nullable();
            $table->json('emergency_contact')->nullable();
            $table->string('national_id')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('account_number')->nullable();
            $table->timestamps();

            $table->unique('employee_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};

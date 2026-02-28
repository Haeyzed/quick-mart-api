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
        Schema::create('salary_structures', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('pay_frequency')->default('monthly');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('is_active');
        });

        Schema::create('salary_components', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // earning, deduction
            $table->boolean('is_taxable')->default(false);
            $table->string('calculation_type')->default('fixed'); // fixed, percentage, formula
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('type');
            $table->index('is_active');
        });

        Schema::create('salary_structure_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('salary_structure_id')->constrained('salary_structures')->cascadeOnDelete();
            $table->foreignId('salary_component_id')->constrained('salary_components')->cascadeOnDelete();
            $table->decimal('amount', 12, 2)->default(0);
            $table->decimal('percentage', 5, 2)->nullable();
            $table->timestamps();

            $table->unique(['salary_structure_id', 'salary_component_id'], 'salary_structure_items_structure_component_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('salary_structure_items');
        Schema::dropIfExists('salary_components');
        Schema::dropIfExists('salary_structures');
    }
};

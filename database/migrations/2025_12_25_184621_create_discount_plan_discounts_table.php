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
        Schema::create('discount_plan_discounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_id');
            $table->unsignedBigInteger('discount_plan_id');
            $table->timestamps();

            // Foreign keys will be added in a later migration
            // after discounts and discount_plans tables are created

            $table->index('discount_id');
            $table->index('discount_plan_id');
            $table->unique(['discount_id', 'discount_plan_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_plan_discounts');
    }
};

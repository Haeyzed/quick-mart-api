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
        Schema::create('discount_plan_customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('discount_plan_id');
            $table->unsignedBigInteger('customer_id');
            $table->timestamps();

            $table->foreign('discount_plan_id')
                ->references('id')
                ->on('discount_plans')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index('discount_plan_id');
            $table->index('customer_id');
            $table->unique(['discount_plan_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_plan_customers');
    }
};

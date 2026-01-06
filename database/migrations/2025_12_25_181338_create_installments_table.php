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
        Schema::create('installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('installment_plan_id');
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamp('payment_date');
            $table->decimal('amount', 15, 2);
            $table->timestamps();

            $table->foreign('installment_plan_id')
                ->references('id')
                ->on('installment_plans')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->index('installment_plan_id');
            $table->index('status');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installments');
    }
};

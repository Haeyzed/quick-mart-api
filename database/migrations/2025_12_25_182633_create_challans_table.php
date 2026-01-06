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
        Schema::create('challans', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 255);
            $table->string('status', 255);
            $table->unsignedBigInteger('courier_id');
            $table->longText('packing_slip_list');
            $table->longText('amount_list');
            $table->longText('cash_list')->nullable();
            $table->longText('online_payment_list')->nullable();
            $table->longText('cheque_list')->nullable();
            $table->longText('delivery_charge_list')->nullable();
            $table->longText('status_list')->nullable();
            $table->date('closing_date')->nullable();
            $table->unsignedBigInteger('created_by_id');
            $table->unsignedBigInteger('closed_by_id')->nullable();
            $table->timestamps();

            $table->foreign('courier_id')
                ->references('id')
                ->on('couriers')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('created_by_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('closed_by_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('reference_no');
            $table->index('courier_id');
            $table->index('status');
            $table->index('created_by_id');
            $table->index('closing_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('challans');
    }
};

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
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('cash_register_id')->nullable();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('biller_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->double('exchange_rate')->nullable();
            $table->integer('item');
            $table->double('total_qty');
            $table->double('total_discount');
            $table->double('total_tax');
            $table->double('total_price');
            $table->double('order_tax_rate')->nullable();
            $table->double('order_tax')->nullable();
            $table->double('grand_total');
            $table->string('document')->nullable();
            $table->text('return_note')->nullable();
            $table->text('staff_note')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('customer_id')
                ->references('id')
                ->on('customers')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('biller_id')
                ->references('id')
                ->on('billers')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('reference_no');
            $table->index('user_id');
            $table->index('sale_id');
            $table->index('customer_id');
            $table->index('warehouse_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};

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
        Schema::create('pos_setting', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('biller_id');
            $table->integer('product_number');
            $table->boolean('keybord_active');
            $table->boolean('is_table')->default(false);
            $table->boolean('send_sms')->default(false);
            $table->string('stripe_public_key')->nullable();
            $table->string('stripe_secret_key')->nullable();
            $table->string('paypal_live_api_username')->nullable();
            $table->string('paypal_live_api_password')->nullable();
            $table->string('paypal_live_api_secret')->nullable();
            $table->text('payment_options')->nullable();
            $table->boolean('show_print_invoice')->default(true);
            $table->string('invoice_option', 10)->nullable();
            $table->string('thermal_invoice_size', 255)->default('80');
            $table->tinyInteger('cash_register')->default(0);
            $table->timestamps();

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

            $table->index('customer_id');
            $table->index('warehouse_id');
            $table->index('biller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pos_setting');
    }
};

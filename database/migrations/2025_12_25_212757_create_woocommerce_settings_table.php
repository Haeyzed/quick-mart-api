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
        Schema::create('woocommerce_settings', function (Blueprint $table) {
            $table->id();
            $table->string('woocomerce_app_url', 255)->nullable();
            $table->string('woocomerce_consumer_key', 255)->nullable();
            $table->string('woocomerce_consumer_secret', 255)->nullable();
            $table->string('default_tax_class', 255)->nullable();
            $table->string('product_tax_type', 255)->nullable();
            $table->string('manage_stock', 255)->nullable();
            $table->string('stock_status', 255)->nullable();
            $table->string('product_status', 255)->nullable();
            $table->unsignedBigInteger('customer_group_id')->nullable();
            $table->unsignedBigInteger('warehouse_id')->nullable();
            $table->unsignedBigInteger('biller_id')->nullable();
            $table->tinyInteger('order_status_pending')->nullable();
            $table->tinyInteger('order_status_processing')->nullable();
            $table->tinyInteger('order_status_on_hold')->nullable();
            $table->tinyInteger('order_status_completed')->nullable();
            $table->tinyInteger('order_status_draft')->nullable();
            $table->string('webhook_secret_order_created', 255)->nullable();
            $table->string('webhook_secret_order_updated', 255)->nullable();
            $table->string('webhook_secret_order_deleted', 255)->nullable();
            $table->string('webhook_secret_order_restored', 255)->nullable();
            $table->timestamps();

            $table->foreign('customer_group_id')
                ->references('id')
                ->on('customer_groups')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('biller_id')
                ->references('id')
                ->on('billers')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('customer_group_id');
            $table->index('warehouse_id');
            $table->index('biller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('woocommerce_settings');
    }
};

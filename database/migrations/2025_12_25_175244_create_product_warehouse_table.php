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
        Schema::create('product_warehouse', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_batch_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->text('imei_number')->nullable();
            $table->unsignedBigInteger('warehouse_id');
            $table->double('qty');
            $table->double('price')->nullable();
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            // Foreign key for product_batch_id will be added in a later migration
            // after product_batches table is created

            $table->foreign('variant_id')
                ->references('id')
                ->on('variants')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('product_id');
            $table->index('warehouse_id');
            $table->index('variant_id');
            $table->index('product_batch_id');
            $table->index(['product_id', 'warehouse_id', 'variant_id', 'product_batch_id'], 'pw_variant_batch_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_warehouse');
    }
};

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
        Schema::create('product_purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_batch_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->text('imei_number')->nullable();
            $table->double('qty');
            $table->double('recieved');
            $table->double('return_qty')->default(0);
            $table->unsignedBigInteger('purchase_unit_id');
            $table->double('net_unit_cost');
            $table->decimal('net_unit_margin', 8, 2)->default(0.00);
            $table->enum('net_unit_margin_type', ['flat', 'percentage'])->default('percentage');
            $table->decimal('net_unit_price', 8, 2)->default(0.00);
            $table->double('discount');
            $table->double('tax_rate');
            $table->double('tax');
            $table->double('total');
            $table->timestamps();

            $table->foreign('purchase_id')
                ->references('id')
                ->on('purchases')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('variant_id')
                ->references('id')
                ->on('variants')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('purchase_unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('purchase_id');
            $table->index('product_id');
            $table->index('variant_id');
            $table->index('purchase_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_purchases');
    }
};

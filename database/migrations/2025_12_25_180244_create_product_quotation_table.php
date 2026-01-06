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
        Schema::create('product_quotation', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('product_batch_id')->nullable();
            $table->unsignedBigInteger('variant_id')->nullable();
            $table->double('qty');
            $table->unsignedBigInteger('sale_unit_id');
            $table->double('net_unit_price');
            $table->double('discount');
            $table->double('tax_rate');
            $table->double('tax');
            $table->double('total');
            $table->timestamps();

            $table->foreign('quotation_id')
                ->references('id')
                ->on('quotations')
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

            $table->foreign('sale_unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('quotation_id');
            $table->index('product_id');
            $table->index('variant_id');
            $table->index('sale_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_quotation');
    }
};

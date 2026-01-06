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
        Schema::create('product_productions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('production_id');
            $table->unsignedBigInteger('product_id');
            $table->double('qty');
            $table->double('recieved');
            $table->unsignedBigInteger('purchase_unit_id');
            $table->double('net_unit_cost');
            $table->double('tax_rate');
            $table->double('tax');
            $table->double('total');
            $table->timestamps();

            $table->foreign('production_id')
                ->references('id')
                ->on('productions')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('purchase_unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('production_id');
            $table->index('product_id');
            $table->index('purchase_unit_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_productions');
    }
};

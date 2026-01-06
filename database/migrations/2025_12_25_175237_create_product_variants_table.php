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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('variant_id');
            $table->integer('position');
            $table->string('item_code');
            $table->double('additional_cost')->nullable();
            $table->double('additional_price')->nullable();
            $table->double('qty');
            $table->timestamps();

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('variant_id')
                ->references('id')
                ->on('variants')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('product_id');
            $table->index('variant_id');
            $table->index('item_code');
            $table->unique(['product_id', 'variant_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};

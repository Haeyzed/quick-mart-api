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
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->unsignedBigInteger('user_id');
            $table->integer('status');
            $table->unsignedBigInteger('from_warehouse_id');
            $table->unsignedBigInteger('to_warehouse_id');
            $table->integer('item');
            $table->double('total_qty');
            $table->double('total_tax');
            $table->double('total_cost');
            $table->double('shipping_cost')->nullable();
            $table->double('grand_total');
            $table->string('document')->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_sent')->default(false);
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('from_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('to_warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('reference_no');
            $table->index('user_id');
            $table->index('from_warehouse_id');
            $table->index('to_warehouse_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};

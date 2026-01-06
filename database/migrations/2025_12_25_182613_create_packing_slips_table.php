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
        Schema::create('packing_slips', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 255);
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('delivery_id')->nullable();
            $table->double('amount');
            $table->string('status', 255);
            $table->timestamps();

            $table->foreign('sale_id')
                ->references('id')
                ->on('sales')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('delivery_id')
                ->references('id')
                ->on('deliveries')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('reference_no');
            $table->index('sale_id');
            $table->index('delivery_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packing_slips');
    }
};

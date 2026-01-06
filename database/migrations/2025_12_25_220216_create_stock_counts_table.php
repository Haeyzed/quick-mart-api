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
        Schema::create('stock_counts', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no', 191);
            $table->unsignedBigInteger('warehouse_id');
            $table->string('category_id', 191)->nullable();
            $table->string('brand_id', 191)->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 191);
            $table->string('initial_file', 191)->nullable();
            $table->string('final_file', 191)->nullable();
            $table->text('note')->nullable();
            $table->boolean('is_adjusted')->default(false);
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('reference_no');
            $table->index('warehouse_id');
            $table->index('user_id');
            $table->index('type');
            $table->index('is_adjusted');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_counts');
    }
};

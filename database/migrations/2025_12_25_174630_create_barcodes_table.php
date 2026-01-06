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
        Schema::create('barcodes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('width', 22, 4)->nullable();
            $table->decimal('height', 22, 4)->nullable();
            $table->decimal('paper_width', 22, 4)->nullable();
            $table->decimal('paper_height', 22, 4)->nullable();
            $table->decimal('top_margin', 22, 4)->nullable();
            $table->decimal('left_margin', 22, 4)->nullable();
            $table->decimal('row_distance', 22, 4)->nullable();
            $table->decimal('col_distance', 22, 4)->nullable();
            $table->integer('stickers_in_one_row')->nullable();
            $table->boolean('is_default')->default(false);
            $table->boolean('is_continuous')->default(false);
            $table->integer('stickers_in_one_sheet')->nullable();
            $table->integer('is_custom')->nullable();
            $table->timestamps();

            $table->index('is_default');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barcodes');
    }
};

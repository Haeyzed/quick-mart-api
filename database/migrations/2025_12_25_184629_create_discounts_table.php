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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('applicable_for');
            $table->longText('product_list')->nullable();
            $table->date('valid_from');
            $table->date('valid_till');
            $table->string('type');
            $table->double('value');
            $table->double('minimum_qty')->nullable();
            $table->double('maximum_qty')->nullable();
            $table->string('days');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index('applicable_for');
            $table->index('valid_from');
            $table->index('valid_till');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};

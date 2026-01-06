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
        Schema::table('discount_plan_discounts', function (Blueprint $table) {
            $table->foreign('discount_id')
                ->references('id')
                ->on('discounts')
                ->onDelete('cascade')
                ->onUpdate('cascade');

            $table->foreign('discount_plan_id')
                ->references('id')
                ->on('discount_plans')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('discount_plan_discounts', function (Blueprint $table) {
            $table->dropForeign(['discount_id']);
            $table->dropForeign(['discount_plan_id']);
        });
    }
};

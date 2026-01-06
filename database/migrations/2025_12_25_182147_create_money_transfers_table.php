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
        Schema::create('money_transfers', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->unsignedBigInteger('from_account_id');
            $table->unsignedBigInteger('to_account_id');
            $table->double('amount');
            $table->timestamps();

            $table->foreign('from_account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('to_account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('reference_no');
            $table->index('from_account_id');
            $table->index('to_account_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('money_transfers');
    }
};

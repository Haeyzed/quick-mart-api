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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('payment_reference');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->unsignedBigInteger('sale_id')->nullable();
            $table->unsignedBigInteger('cash_register_id')->nullable();
            $table->unsignedBigInteger('account_id');
            $table->string('payment_receiver', 255)->nullable();
            $table->double('amount');
            $table->unsignedBigInteger('currency_id')->nullable();
            $table->unsignedBigInteger('installment_id')->nullable();
            $table->decimal('exchange_rate', 8, 2)->default(1.00);
            $table->timestamp('payment_at')->nullable();
            $table->double('used_points')->nullable();
            $table->double('change')->nullable();
            $table->string('paying_method');
            $table->text('payment_note')->nullable();
            $table->string('document')->nullable();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('currency_id')
                ->references('id')
                ->on('currencies')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('payment_reference');
            $table->index('user_id');
            $table->index('purchase_id');
            $table->index('sale_id');
            $table->index('account_id');
            $table->index('payment_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

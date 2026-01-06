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
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->string('reference_no');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('account_id');
            $table->unsignedBigInteger('user_id');
            $table->double('amount');
            $table->string('paying_method');
            $table->text('note')->nullable();
            $table->string('status', 50)->default('draft');
            $table->longText('amount_array')->nullable();
            $table->string('month')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')
                ->references('id')
                ->on('employees')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('reference_no');
            $table->index('employee_id');
            $table->index('account_id');
            $table->index('user_id');
            $table->index('status');
            $table->index('month');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};

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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('account_no');
            $table->string('name');
            $table->double('initial_balance')->nullable();
            $table->double('total_balance');
            $table->text('note')->nullable();
            $table->boolean('is_default')->nullable();
            $table->boolean('is_active');
            $table->string('code', 255)->nullable();
            $table->string('type', 255)->default('Bank Account');
            $table->unsignedBigInteger('parent_account_id')->nullable();
            $table->boolean('is_payment')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_account_id')
                ->references('id')
                ->on('accounts')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('account_no');
            $table->index('parent_account_id');
            $table->index('is_default');
            $table->index('is_active');
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};

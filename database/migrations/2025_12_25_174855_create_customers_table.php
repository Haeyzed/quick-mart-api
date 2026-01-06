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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_group_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->nullable();
            $table->enum('type', ['regular', 'walkin'])->default('regular');
            $table->string('phone_number');
            $table->string('wa_number')->nullable();
            $table->string('tax_no')->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->double('opening_balance')->default(0);
            $table->double('credit_limit')->nullable();
            $table->double('points')->nullable();
            $table->double('deposit')->nullable();
            $table->integer('pay_term_no')->nullable();
            $table->string('pay_term_period')->nullable();
            $table->double('expense')->nullable();
            $table->longText('wishlist')->nullable();
            $table->boolean('is_active')->nullable();
            $table->string('ecom', 255)->nullable();
            $table->string('dsf', 255)->default('df');
            $table->string('arabic_name', 255)->nullable();
            $table->string('admin', 255)->nullable();
            $table->string('franchise_location', 255)->nullable();
            $table->string('customer_type', 255)->default('Same as Customer');
            $table->string('customer_assigned_to', 255)->default('Advocate');
            $table->string('assigned', 255)->default('Advocate');
            $table->string('aaaaaaaa', 255)->default('aa');
            $table->string('district', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('customer_group_id')
                ->references('id')
                ->on('customer_groups')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('customer_group_id');
            $table->index('user_id');
            $table->index('email');
            $table->index('phone_number');
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};

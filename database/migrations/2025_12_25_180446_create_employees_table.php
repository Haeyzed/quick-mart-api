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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone_number');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('staff_id')->nullable();
            $table->string('image')->nullable();
            $table->string('image_url')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('country_id')
                ->nullable()
                ->constrained('countries')
                ->nullOnDelete();
            $table->foreignId('state_id')
                ->nullable()
                ->constrained('states')
                ->nullOnDelete();
            $table->foreignId('city_id')
                ->nullable()
                ->constrained('cities')
                ->nullOnDelete();
            $table->boolean('is_active');
            $table->boolean('is_sale_agent')->default(false);
            $table->decimal('sale_commission_percent', 8, 2)->nullable();
            $table->longText('sales_target')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();
            $table->unsignedBigInteger('shift_id')->nullable();
            $table->decimal('basic_salary', 12, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys for department_id, designation_id, and shift_id will be added in later migrations
            // after departments, designations, and shifts tables are created

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('email');
            $table->index('phone_number');
            $table->index('department_id');
            $table->index('user_id');
            $table->index('is_active');
            $table->index('is_sale_agent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};

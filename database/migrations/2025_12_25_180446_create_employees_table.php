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
            $table->decimal('basic_salary', 12, 2)->default(0.00);
            $table->timestamps();
            $table->softDeletes();

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index('email');
            $table->index('phone_number');
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

<?php

use App\Enums\PaymentMethodEnum;
use App\Enums\PayrollStatusEnum;
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
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('account_id')
                ->constrained('accounts')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('user_id')
                ->constrained('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->double('amount');
            $table->string('paying_method')->default(PaymentMethodEnum::CASH->value);
            $table->text('note')->nullable();
            $table->string('status')->default(PayrollStatusEnum::DRAFT->value);
            $table->json('amount_array')->nullable();
            $table->string('month')->nullable();
            $table->timestamps();
            $table->softDeletes();

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

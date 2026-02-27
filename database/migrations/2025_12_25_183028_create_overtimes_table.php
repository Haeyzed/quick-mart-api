<?php

use App\Enums\OvertimeStatusEnum;
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
        Schema::create('overtimes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained('employees')
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->decimal('rate', 10, 2);
            $table->decimal('amount', 10, 2)->default(0.00);
            $table->string('status')->default(OvertimeStatusEnum::PENDING->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index('date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('overtimes');
    }
};

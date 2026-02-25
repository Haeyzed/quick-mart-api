<?php

use App\Enums\LeaveStatusEnum;
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
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')
                ->constrained()
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->foreignId('leave_type_id')
                ->constrained()
                ->restrictOnDelete()
                ->cascadeOnUpdate();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('days');
            $table->string('status')->default(LeaveStatusEnum::PENDING->value);
            $table->foreignId('approver_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
            $table->timestamps();
            $table->softDeletes();

            $table->index('employee_id');
            $table->index('leave_type_id');
            $table->index('approver_id');
            $table->index('status');
            $table->index('start_date');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leaves');
    }
};

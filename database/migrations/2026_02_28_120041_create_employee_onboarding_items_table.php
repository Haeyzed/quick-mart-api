<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_onboarding_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_onboarding_id')->constrained('employee_onboarding')->cascadeOnDelete();
            $table->foreignId('onboarding_checklist_item_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['employee_onboarding_id', 'onboarding_checklist_item_id'], 'emp_onb_items_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_onboarding_items');
    }
};

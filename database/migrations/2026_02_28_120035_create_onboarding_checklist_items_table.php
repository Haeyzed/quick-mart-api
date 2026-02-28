<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('onboarding_checklist_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('onboarding_checklist_template_id');
            $table->foreign('onboarding_checklist_template_id', 'onb_items_tpl_fk')->references('id')->on('onboarding_checklist_templates')->cascadeOnDelete();
            $table->string('title');
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('onboarding_checklist_items');
    }
};

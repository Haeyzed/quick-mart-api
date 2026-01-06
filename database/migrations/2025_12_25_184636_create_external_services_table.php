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
        Schema::create('external_services', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('type', 255)->nullable();
            $table->longText('details')->nullable();
            $table->longText('module_status')->nullable();
            $table->integer('active')->default(1);
            $table->timestamps();

            $table->index('type');
            $table->index('active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('external_services');
    }
};

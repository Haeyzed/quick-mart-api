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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('image')->nullable();
            $table->string('image_url')->nullable();
            $table->string('icon_url')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('page_title')->nullable();
            $table->text('short_description')->nullable();
            $table->string('slug')->nullable();
            $table->string('icon')->nullable();
            $table->tinyInteger('featured')->default(1);
            $table->boolean('is_active')->nullable();
            $table->integer('woocommerce_category_id')->nullable();
            $table->tinyInteger('is_sync_disable')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('parent_id')
                ->references('id')
                ->on('categories')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('parent_id');
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};

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
        Schema::create('ecommerce_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title')->nullable();
            $table->string('theme')->default('default');
            $table->string('theme_font')->default('Inter');
            $table->string('theme_color')->default('#fa9928');
            $table->string('logo')->nullable();
            $table->string('favicon')->nullable();
            $table->string('store_phone')->nullable();
            $table->string('store_email')->nullable();
            $table->string('store_address')->nullable();
            $table->bigInteger('home_page')->nullable();
            $table->integer('online_order')->default(1);
            $table->integer('is_rtl')->default(0);
            $table->integer('search')->default(0);
            $table->unsignedBigInteger('warehouse_id');
            $table->unsignedBigInteger('biller_id');
            $table->string('contact_form_email')->nullable();
            $table->double('free_shipping_from')->nullable();
            $table->double('flat_rate_shipping')->nullable();
            $table->longText('checkout_pages')->nullable();
            $table->tinyInteger('gift_card')->default(0);
            $table->longText('custom_css')->nullable();
            $table->longText('custom_js')->nullable();
            $table->text('chat_code')->nullable();
            $table->text('analytics_code')->nullable();
            $table->text('fb_pixel_code')->nullable();
            $table->string('tktk_pixel_code')->nullable();
            $table->integer('sell_without_stock')->default(0);
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('biller_id')
                ->references('id')
                ->on('billers')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('warehouse_id');
            $table->index('biller_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ecommerce_settings');
    }
};

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
        Schema::create('page_widgets', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('page_id');
            $table->string('order');
            $table->string('product_category_title')->nullable();
            $table->string('product_category_id')->nullable();
            $table->string('product_category_type')->nullable();
            $table->string('product_category_slider_loop')->nullable();
            $table->string('product_category_slider_autoplay')->nullable();
            $table->string('product_category_limit')->nullable();
            $table->string('tab_product_collection_id')->nullable();
            $table->string('tab_product_collection_type')->nullable();
            $table->string('tab_product_collection_slider_loop')->nullable();
            $table->string('tab_product_collection_slider_autoplay')->nullable();
            $table->string('tab_product_collection_limit')->nullable();
            $table->string('product_collection_title')->nullable();
            $table->string('product_collection_id')->nullable();
            $table->string('product_collection_type')->nullable();
            $table->string('product_collection_slider_loop')->nullable();
            $table->string('product_collection_slider_autoplay')->nullable();
            $table->string('product_collection_limit')->nullable();
            $table->string('category_slider_title')->nullable();
            $table->string('category_slider_loop')->nullable();
            $table->string('category_slider_autoplay')->nullable();
            $table->string('category_slider_ids')->nullable();
            $table->string('brand_slider_title')->nullable();
            $table->string('brand_slider_loop')->nullable();
            $table->string('brand_slider_autoplay')->nullable();
            $table->string('brand_slider_ids')->nullable();
            $table->string('three_c_banner_link1')->nullable();
            $table->string('three_c_banner_image1')->nullable();
            $table->string('three_c_banner_link2')->nullable();
            $table->string('three_c_banner_image2')->nullable();
            $table->string('three_c_banner_link3')->nullable();
            $table->string('three_c_banner_image3')->nullable();
            $table->string('two_c_banner_link1')->nullable();
            $table->string('two_c_banner_image1')->nullable();
            $table->string('two_c_banner_link2')->nullable();
            $table->string('two_c_banner_image2')->nullable();
            $table->string('one_c_banner_link1')->nullable();
            $table->string('one_c_banner_image1')->nullable();
            $table->string('text_title')->nullable();
            $table->string('text_content')->nullable();
            $table->text('slider_images')->nullable();
            $table->text('slider_links')->nullable();
            $table->timestamps();

            $table->index('page_id');
            $table->index('order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('page_widgets');
    }
};

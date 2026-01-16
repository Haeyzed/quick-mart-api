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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->string('tags')->nullable();
            $table->string('code');
            $table->string('type');
            $table->string('barcode_symbology');
            $table->unsignedBigInteger('brand_id')->nullable();
            $table->unsignedBigInteger('category_id');
            $table->unsignedBigInteger('unit_id');
            $table->unsignedBigInteger('purchase_unit_id');
            $table->unsignedBigInteger('sale_unit_id');
            $table->double('cost');
            $table->double('price');
            $table->decimal('profit_margin', 8, 2)->default(0.00);
            $table->enum('profit_margin_type', ['flat', 'percentage'])->default('percentage');
            $table->double('wholesale_price')->nullable();
            $table->double('qty')->nullable();
            $table->double('alert_quantity')->nullable();
            $table->double('daily_sale_objective')->nullable();
            $table->tinyInteger('promotion')->nullable();
            $table->string('promotion_price')->nullable();
            $table->string('starting_date', 200)->nullable();
            $table->date('last_date')->nullable();
            $table->unsignedBigInteger('tax_id')->nullable();
            $table->integer('tax_method')->nullable();
            $table->longText('image')->nullable();
            $table->string('file')->nullable();
            $table->boolean('is_embeded')->nullable();
            $table->boolean('is_variant')->nullable();
            $table->boolean('is_batch')->nullable();
            $table->boolean('is_diff_price')->nullable();
            $table->boolean('is_imei')->nullable();
            $table->tinyInteger('featured')->nullable();
            $table->tinyInteger('is_online')->nullable();
            $table->tinyInteger('in_stock')->nullable();
            $table->tinyInteger('track_inventory')->default(0);
            $table->string('product_list')->nullable();
            $table->string('variant_list')->nullable();
            $table->string('qty_list')->nullable();
            $table->string('price_list')->nullable();
            $table->text('product_details')->nullable();
            $table->text('short_description')->nullable();
            $table->text('specification')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->longText('related_products')->nullable();
            $table->text('variant_option')->nullable();
            $table->text('variant_value')->nullable();
            $table->boolean('is_active')->nullable();
            $table->tinyInteger('is_sync_disable')->nullable();
            $table->integer('woocommerce_product_id')->nullable();
            $table->integer('woocommerce_media_id')->nullable();
            $table->integer('guarantee')->nullable();
            $table->integer('warranty')->nullable();
            $table->string('guarantee_type', 255)->nullable();
            $table->string('warranty_type', 255)->nullable();
            $table->string('wastage_percent')->default('0');
            $table->string('combo_unit_id')->nullable();
            $table->string('production_cost')->default('0');
            $table->unsignedBigInteger('is_recipe')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('brand_id')
                ->references('id')
                ->on('brands')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('purchase_unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('sale_unit_id')
                ->references('id')
                ->on('units')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('tax_id')
                ->references('id')
                ->on('taxes')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('code');
            $table->index('slug');
            $table->index('brand_id');
            $table->index('category_id');
            $table->index('unit_id');
            $table->index('tax_id');
            $table->index('type');
            $table->index('is_active');
            $table->index('is_variant');
            $table->index('is_batch');
            $table->index('is_imei');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

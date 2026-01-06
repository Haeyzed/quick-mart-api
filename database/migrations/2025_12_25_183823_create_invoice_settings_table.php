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
        Schema::create('invoice_settings', function (Blueprint $table) {
            $table->id();
            $table->string('template_name');
            $table->string('invoice_name')->nullable();
            $table->string('invoice_logo')->nullable();
            $table->string('file_type')->nullable();
            $table->string('prefix')->nullable();
            $table->string('number_of_digit')->nullable();
            $table->string('numbering_type')->nullable();
            $table->unsignedBigInteger('start_number')->nullable();
            $table->unsignedBigInteger('last_invoice_number')->nullable();
            $table->text('header_text')->nullable();
            $table->string('header_title')->nullable();
            $table->text('footer_text')->nullable();
            $table->string('footer_title')->nullable();
            $table->string('preview_invoice')->nullable();
            $table->string('size')->nullable();
            $table->string('primary_color')->nullable();
            $table->string('secondary_color')->nullable();
            $table->string('text_color')->nullable();
            $table->string('company_logo')->nullable();
            $table->string('logo_height')->nullable();
            $table->string('logo_width')->nullable();
            $table->boolean('is_default')->default(false)->comment('0=not defoult, 1= defoult');
            $table->boolean('status')->default(false);
            $table->string('invoice_date_format')->default('Y-M-d h:m:s');
            $table->longText('show_column')->nullable();
            $table->longText('extra')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->foreign('updated_by')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');

            $table->index('template_name');
            $table->index('is_default');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_settings');
    }
};

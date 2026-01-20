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
        Schema::create('general_settings', function (Blueprint $table) {
            $table->id();
            $table->string('site_title');
            $table->string('site_logo')->nullable();
            $table->string('favicon')->nullable();
            $table->boolean('is_rtl')->nullable();
            $table->string('currency');
            $table->integer('package_id')->nullable();
            $table->string('subscription_type')->nullable();
            $table->string('staff_access');
            $table->string('without_stock')->default('no');
            $table->string('date_format');
            $table->string('developed_by')->nullable();
            $table->string('invoice_format')->nullable();
            $table->integer('decimal')->default(2);
            $table->integer('state')->nullable();
            $table->string('theme');
            $table->text('modules')->nullable();
            $table->string('currency_position');
            $table->date('expiry_date')->nullable();
            $table->string('expiry_type')->default('days');
            $table->string('expiry_value')->default('0');
            $table->unsignedInteger('expiry_alert_days')->default(0)->comment('Number of days before expiry to show alert');
            $table->boolean('is_zatca')->nullable();
            $table->string('company_name')->nullable();
            $table->string('vat_registration_number')->nullable();
            $table->boolean('is_packing_slip')->default(false);
            $table->string('app_key', 100)->nullable();
            $table->string('token')->nullable();
            $table->boolean('show_products_details_in_sales_table')->default(false);
            $table->boolean('show_products_details_in_purchase_table')->default(false);
            $table->decimal('default_margin_value', 8, 2)->default(25.00);
            $table->string('timezone')->nullable();
            $table->text('font_css')->nullable();
            $table->longText('auth_css')->nullable();
            $table->longText('pos_css')->nullable();
            $table->longText('custom_css')->nullable();
            $table->integer('disable_signup')->default(0);
            $table->integer('disable_forgot_password')->default(0);
            $table->integer('margin_type')->default(0);
            $table->string('storage_provider')->default('public')->comment('Storage provider name (public, s3, etc.)');
            $table->string('google_client_id')->nullable()->comment('Google OAuth Client ID');
            $table->string('google_client_secret')->nullable()->comment('Google OAuth Client Secret');
            $table->string('google_redirect_url')->nullable()->comment('Google OAuth Redirect URL');
            $table->boolean('google_login_enabled')->default(false)->comment('Enable Google login');
            $table->string('facebook_client_id')->nullable()->comment('Facebook OAuth App ID');
            $table->string('facebook_client_secret')->nullable()->comment('Facebook OAuth App Secret');
            $table->string('facebook_redirect_url')->nullable()->comment('Facebook OAuth Redirect URL');
            $table->boolean('facebook_login_enabled')->default(false)->comment('Enable Facebook login');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('general_settings');
    }
};

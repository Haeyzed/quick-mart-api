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
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone_number')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone_number');
            $table->unsignedBigInteger('biller_id')->nullable()->after('company_name');
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('biller_id');
            $table->unsignedBigInteger('kitchen_id')->nullable()->after('warehouse_id');
            $table->boolean('service_staff')->default(false)->after('kitchen_id');
            $table->boolean('is_active')->default(true)->after('service_staff');

            // Add foreign keys
            $table->foreign('biller_id')->references('id')->on('billers')->onDelete('set null');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['biller_id']);
            $table->dropForeign(['warehouse_id']);

            $table->dropColumn([
                'phone_number',
                'company_name',
                'biller_id',
                'warehouse_id',
                'kitchen_id',
                'service_staff',
                'is_active',
            ]);
        });
    }
};


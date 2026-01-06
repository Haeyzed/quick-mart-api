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
            $table->string('phone')->nullable()->after('email');
            $table->string('company_name')->nullable()->after('phone');
            $table->unsignedBigInteger('role_id')->nullable()->after('company_name');
            $table->unsignedBigInteger('biller_id')->nullable()->after('role_id');
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('biller_id');
            $table->unsignedBigInteger('kitchen_id')->nullable()->after('warehouse_id');
            $table->boolean('service_staff')->default(false)->after('kitchen_id');
            $table->boolean('is_active')->default(true)->after('service_staff');
            $table->boolean('is_deleted')->default(false)->after('is_active');

            // Add foreign keys
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('set null');
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
            $table->dropForeign(['role_id']);
            $table->dropForeign(['biller_id']);
            $table->dropForeign(['warehouse_id']);
            
            $table->dropColumn([
                'phone',
                'company_name',
                'role_id',
                'biller_id',
                'warehouse_id',
                'kitchen_id',
                'service_staff',
                'is_active',
                'is_deleted',
            ]);
        });
    }
};


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
        Schema::create('printers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('warehouse_id');
            $table->enum('connection_type', ['network', 'windows', 'linux']);
            $table->enum('capability_profile', ['default', 'simple', 'SP2000', 'TEP-200M', 'TM-U220', 'RP326', 'P822D'])->default('default');
            $table->integer('char_per_line')->default(42);
            $table->string('ip_address')->nullable();
            $table->string('port')->nullable();
            $table->string('path')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('created_by')
                ->references('id')
                ->on('users')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->index('warehouse_id');
            $table->index('created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('printers');
    }
};

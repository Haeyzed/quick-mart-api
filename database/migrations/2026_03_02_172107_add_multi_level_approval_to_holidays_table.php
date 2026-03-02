<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('holidays', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('pending')->after('is_approved');
            $table->unsignedTinyInteger('current_approval_level')->default(0)->after('approval_status');
            $table->unsignedTinyInteger('max_approval_level')->default(2)->after('current_approval_level');
        });

        Schema::create('holiday_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('holiday_id')->constrained('holidays')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20); // approved, rejected
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['holiday_id', 'level']);
        });

        DB::table('holidays')->where('is_approved', true)->update(['approval_status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('holiday_approvals');

        Schema::table('holidays', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'current_approval_level', 'max_approval_level']);
        });
    }
};

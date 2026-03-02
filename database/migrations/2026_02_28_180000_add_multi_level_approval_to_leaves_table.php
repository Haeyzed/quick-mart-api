<?php

declare(strict_types=1);

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
        Schema::table('leaves', function (Blueprint $table) {
            $table->string('approval_status', 20)->default('pending')->after('status');
            $table->unsignedTinyInteger('current_approval_level')->default(0)->after('approval_status');
            $table->unsignedTinyInteger('max_approval_level')->default(2)->after('current_approval_level');
        });

        Schema::create('leave_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('leave_id')->constrained('leaves')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 20);
            $table->text('notes')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['leave_id', 'level']);
        });

        DB::table('leaves')->where('status', 'approved')->update(['approval_status' => 'approved']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leave_approvals');

        Schema::table('leaves', function (Blueprint $table) {
            $table->dropColumn(['approval_status', 'current_approval_level', 'max_approval_level']);
        });
    }
};

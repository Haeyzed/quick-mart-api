<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('code', 64)->nullable()->unique()->after('name');
            $table->boolean('requires_expiry')->default(false)->after('code');
            $table->boolean('is_active')->default(true)->after('requires_expiry');
            $table->softDeletes();
            $table->index('is_active');
        });

        Schema::table('employee_documents', function (Blueprint $table) {
            $table->foreignId('employee_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('document_type_id')->after('employee_id')->constrained()->cascadeOnDelete();
            $table->string('name')->nullable()->after('document_type_id');
            $table->string('file_path')->nullable()->after('name');
            $table->string('file_url', 500)->nullable()->after('file_path');
            $table->date('issue_date')->nullable()->after('file_url');
            $table->date('expiry_date')->nullable()->after('issue_date');
            $table->text('notes')->nullable()->after('expiry_date');
            $table->index(['employee_id', 'document_type_id']);
        });

        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->foreignId('employee_id')->after('id')->constrained()->cascadeOnDelete();
            $table->date('review_period_start')->after('employee_id');
            $table->date('review_period_end')->after('review_period_start');
            $table->foreignId('reviewer_id')->nullable()->after('review_period_end')->constrained('users')->nullOnDelete();
            $table->decimal('overall_rating', 4, 2)->nullable()->after('reviewer_id');
            $table->string('status', 32)->default('draft')->after('overall_rating');
            $table->text('notes')->nullable()->after('status');
            $table->date('promotion_effective_date')->nullable()->after('notes');
            $table->foreignId('new_designation_id')->nullable()->after('promotion_effective_date')->constrained('designations')->nullOnDelete();
            $table->timestamp('submitted_at')->nullable()->after('new_designation_id');
            $table->index(['employee_id', 'review_period_end']);
            $table->index('status');
        });

        Schema::table('job_openings', function (Blueprint $table) {
            $table->string('title')->after('id');
            $table->foreignId('department_id')->nullable()->after('title')->constrained()->nullOnDelete();
            $table->foreignId('designation_id')->nullable()->after('department_id')->constrained()->nullOnDelete();
            $table->string('status', 32)->default('draft')->after('designation_id');
            $table->text('description')->nullable()->after('status');
            $table->unsignedInteger('openings_count')->default(1)->after('description');
            $table->foreignId('created_by')->nullable()->after('openings_count')->constrained('users')->nullOnDelete();
            $table->softDeletes();
            $table->index('status');
        });

        Schema::table('candidates', function (Blueprint $table) {
            $table->foreignId('job_opening_id')->after('id')->constrained()->cascadeOnDelete();
            $table->string('name')->after('job_opening_id');
            $table->string('email')->after('name');
            $table->string('phone', 50)->nullable()->after('email');
            $table->string('source', 100)->nullable()->after('phone');
            $table->string('stage', 32)->default('applied')->after('source');
            $table->timestamp('stage_updated_at')->nullable()->after('stage');
            $table->text('notes')->nullable()->after('stage_updated_at');
            $table->softDeletes();
            $table->index(['job_opening_id', 'stage']);
        });

        Schema::table('interviews', function (Blueprint $table) {
            $table->foreignId('candidate_id')->after('id')->constrained()->cascadeOnDelete();
            $table->dateTime('scheduled_at')->after('candidate_id');
            $table->unsignedSmallInteger('duration_minutes')->nullable()->after('scheduled_at');
            $table->foreignId('interviewer_id')->nullable()->after('duration_minutes')->constrained('users')->nullOnDelete();
            $table->text('feedback')->nullable()->after('interviewer_id');
            $table->string('status', 32)->default('scheduled')->after('feedback');
            $table->index(['candidate_id', 'scheduled_at']);
        });

        Schema::table('onboarding_checklist_templates', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->boolean('is_default')->default(false)->after('name');
        });

        Schema::table('employee_onboarding', function (Blueprint $table) {
            $table->foreignId('employee_id')->after('id')->constrained()->cascadeOnDelete();
            $table->foreignId('onboarding_checklist_template_id')->after('employee_id')->constrained()->cascadeOnDelete();
            $table->string('status', 32)->default('pending')->after('onboarding_checklist_template_id');
            $table->timestamp('started_at')->nullable()->after('status');
            $table->timestamp('completed_at')->nullable()->after('started_at');
            $table->unique(['employee_id', 'onboarding_checklist_template_id'], 'emp_onb_emp_tpl_unique');
        });
    }

    public function down(): void
    {
        Schema::table('document_types', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropColumn(['name', 'code', 'requires_expiry', 'is_active', 'deleted_at']);
        });
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['document_type_id']);
            $table->dropIndex(['employee_id', 'document_type_id']);
            $table->dropColumn(['employee_id', 'document_type_id', 'name', 'file_path', 'file_url', 'issue_date', 'expiry_date', 'notes']);
        });
        Schema::table('performance_reviews', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['reviewer_id']);
            $table->dropForeign(['new_designation_id']);
            $table->dropIndex(['employee_id', 'review_period_end']);
            $table->dropIndex(['status']);
            $table->dropColumn(['employee_id', 'review_period_start', 'review_period_end', 'reviewer_id', 'overall_rating', 'status', 'notes', 'promotion_effective_date', 'new_designation_id', 'submitted_at']);
        });
        Schema::table('job_openings', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['created_by']);
            $table->dropIndex(['status']);
            $table->dropColumn(['title', 'department_id', 'designation_id', 'status', 'description', 'openings_count', 'created_by', 'deleted_at']);
        });
        Schema::table('candidates', function (Blueprint $table) {
            $table->dropForeign(['job_opening_id']);
            $table->dropIndex(['job_opening_id', 'stage']);
            $table->dropColumn(['job_opening_id', 'name', 'email', 'phone', 'source', 'stage', 'stage_updated_at', 'notes', 'deleted_at']);
        });
        Schema::table('interviews', function (Blueprint $table) {
            $table->dropForeign(['candidate_id']);
            $table->dropForeign(['interviewer_id']);
            $table->dropIndex(['candidate_id', 'scheduled_at']);
            $table->dropColumn(['candidate_id', 'scheduled_at', 'duration_minutes', 'interviewer_id', 'feedback', 'status']);
        });
        Schema::table('onboarding_checklist_templates', function (Blueprint $table) {
            $table->dropColumn(['name', 'is_default']);
        });
        Schema::table('employee_onboarding', function (Blueprint $table) {
            $table->dropUnique('emp_onb_emp_tpl_unique');
            $table->dropForeign(['employee_id']);
            $table->dropForeign(['onboarding_checklist_template_id']);
            $table->dropColumn(['employee_id', 'onboarding_checklist_template_id', 'status', 'started_at', 'completed_at']);
        });
    }
};

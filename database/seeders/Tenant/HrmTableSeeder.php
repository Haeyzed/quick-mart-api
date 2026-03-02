<?php

declare(strict_types=1);

namespace Database\Seeders\Tenant;

use App\Models\Candidate;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\Interview;
use App\Models\JobOpening;
use App\Models\Leave;
use App\Models\OnboardingChecklistItem;
use App\Models\OnboardingChecklistTemplate;
use App\Models\Overtime;
use App\Models\PayrollEntry;
use App\Models\PayrollRun;
use App\Models\PerformanceReview;
use App\Models\Shift;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HrmTableSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function () {
            $department = Department::query()->create([
                'name' => 'Human Resources',
                // add any required extra fields here
            ]);

            $designation = Designation::query()->create([
                'name' => 'HR Manager',
                'department_id' => $department->id,
            ]);

            $shift = Shift::query()->create([
                'name' => 'General',
                'start_time' => '09:00:00',
                'end_time' => '17:00:00',
            ]);

            $employee = Employee::query()->create([
                // fill the required HR fields for your Employee model
                'department_id' => $department->id,
                'designation_id' => $designation->id,
                'shift_id' => $shift->id,
                // …
            ]);

            $holiday = Holiday::query()->create([
                'user_id' => $employee->user_id ?? 1,
                'from_date' => now()->toDateString(),
                'to_date' => now()->toDateString(),
                'note' => 'Seed holiday',
                'is_approved' => true,
                'recurring' => false,
                'region' => 'Global',
            ]);

            $jobOpening = JobOpening::query()->create([
                'title' => 'Seed Job Opening',
                'status' => 'open',
                // set department/designation IDs if required by your model
            ]);

            $candidate = Candidate::query()->create([
                'job_opening_id' => $jobOpening->id,
                'name' => 'Seed Candidate',
                'email' => 'candidate@example.com',
                'stage' => 'applied',
            ]);

            $interview = Interview::query()->create([
                'candidate_id' => $candidate->id,
                'scheduled_at' => now()->addDay(),
                'status' => 'scheduled',
            ]);

            $overtime = Overtime::query()->create([
                'employee_id' => $employee->id,
                'date' => now()->toDateString(),
                'hours' => 2,
                'status' => 'approved',
            ]);

            $payrollRun = PayrollRun::query()->create([
                'month' => now()->format('Y-m'),
                'year' => (int) now()->format('Y'),
                'status' => 'completed',
            ]);

            PayrollEntry::query()->create([
                'payroll_run_id' => $payrollRun->id,
                'employee_id' => $employee->id,
                // amounts matching your model
            ]);

            $performance = PerformanceReview::query()->create([
                'employee_id' => $employee->id,
                'review_period_start' => now()->startOfYear()->toDateString(),
                'review_period_end' => now()->endOfYear()->toDateString(),
                'status' => 'submitted',
            ]);

            $leave = Leave::query()->create([
                'employee_id' => $employee->id,
                'leave_type_id' => 1, // adjust to an existing leave_type
                'start_date' => now()->toDateString(),
                'end_date' => now()->addDay()->toDateString(),
                'days' => 2,
                'status' => 'approved',
                'approval_status' => 'approved',
                'current_approval_level' => 1,
                'max_approval_level' => 1,
            ]);

            $template = OnboardingChecklistTemplate::query()->create([
                'name' => 'Default Onboarding',
                'is_default' => true,
            ]);

            OnboardingChecklistItem::query()->create([
                'onboarding_checklist_template_id' => $template->id,
                'title' => 'Sign contract',
                'sort_order' => 1,
            ]);
        });
    }
}

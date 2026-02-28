# HRM Module — Architecture Gaps Analysis

**Scope:** Laravel API (quick-mart-api) + Next.js App Router + Shadcn UI (ecommerece-frontend)  
**Objective:** Upgrade to enterprise-grade HRM (SAP SuccessFactors / Oracle HCM / BambooHR style) while staying e-commerce and warehouse-aware.

---

## 1. Backend (Laravel API) — Current State

### 1.1 Models & Database

| Area | Current | Gap |
|------|---------|-----|
| **Employee** | `employees`: name, email, phone, staff_id, image, address, country/state/city, is_active, is_sale_agent, commission, sales_target, basic_salary, user_id, department_id, designation_id, shift_id | Missing: `employee_code` (auto), `employment_type`, `joining_date`, `confirmation_date`, `probation_period`, `reporting_manager_id`, `warehouse_id`, `work_location_id`, `salary_structure_id`, `employment_status` lifecycle (active/suspended/resigned/terminated), profile completeness. No separate `employee_profiles` (DOB, gender, marital_status, emergency_contact, national_id, tax_number, bank_name, account_number). |
| **Attendance** | `attendances`: date, employee_id, user_id, checkin, checkout (varchar), status, note | Missing: `shift_id`, `late_minutes`, `early_exit_minutes`, `worked_hours`, `overtime_minutes`, `checkin_source` (device/web), `latitude`, `longitude`. No `attendance_adjustments` for HR corrections. Status derived from global HRM setting only, not per-shift. |
| **Shift** | `shifts`: name, start_time, end_time, grace_in, grace_out, total_hours, is_active | Missing: `break_duration`, `is_rotational`, `overtime_allowed`. No `employee_shift_assignments` (effective_from / effective_to) for rotating/flexible shifts. |
| **Leave** | `leaves`: employee_id, leave_type_id, start/end_date, days, status, approver_id. `leave_types`: name, annual_quota, encashable, carry_forward_limit | Missing: multi-level approval workflow, `leave_balances` table, leave accrual automation, encashment rules implementation. No `reason` or `attachment` on leaves. |
| **Payroll** | `payrolls`: reference_no, employee_id, account_id, user_id, amount, paying_method, note, status, amount_array (JSON), month | **Critical:** No Salary Structure engine. Salary stored on `employees.basic_salary`; payroll is flat amount + JSON breakdown (salary/commission/expense/overtime). Missing: `salary_structures`, `salary_components`, `salary_structure_items`, `payroll_runs`, `payroll_entries`, `payroll_entry_items`. No tax/pension/allowance components, no automated payslip generation API. |
| **Department** | `departments`: name, is_active | Missing: `parent_id`, `manager_id` for org hierarchy / org chart. |
| **Designation** | `designations`: name, is_active | Adequate for current scope. |
| **Overtime** | `overtimes`: employee_id, date, hours, rate, amount, status | No link to attendance or shift; no auto-calculation from attendance. |
| **Holiday** | `holidays`: user_id, from_date, to_date, recurring, region, note, is_approved | No `name`; user_id is creator not “applicable to”. Adequate for basic calendar. |
| **HRM Settings** | `hrm_settings`: checkin, checkout (global default only) | Single row; no per-shift or per-warehouse rules. |
| **Expense** | `expenses`: reference_no, expense_category_id, warehouse_id, account_id, user_id, employee_id (nullable), type, amount, note, document | Exists but not HR-centric: no approval workflow, no reimbursement status, no payroll integration for employee claims. |

### 1.2 Services & Business Logic

- **EmployeeService:** Create/update handles user + roles; no `employee_code` generation, no warehouse filter in `getOptions()` (controller passes `warehouse_id` but service ignores it).
- **AttendanceService:** Late vs present based on global `hrm_settings` checkin; no shift-aware grace, no geo/source, no late_minutes/early_exit_minutes/worked_hours. Device punch and web punch exist; no attendance approval workflow.
- **PayrollService:** Sophisticated `getGenerationData()` (attendance, leave, overtime, expense, commission) and `processBulkPayrolls()`; but amount logic is hardcoded (salary from employee, commission from sales target, expense/overtime from aggregates). No salary structure or component engine; no payslip resource/API.
- **LeaveService:** Standard CRUD + bulk approve/reject; no balance tracking, no accrual, no carry-forward or encashment execution.
- **ShiftService / DepartmentService / DesignationService / LeaveTypeService:** Standard CRUD + options; no hierarchy (department), no rotational/flex rules (shift).

### 1.3 API & Conventions

- **Controllers:** Consistent pattern: permission check → validate → service → Resource → `response()->success()`. Form Requests and API Resources used. DocBlocks present.
- **Policies:** Permission strings used (e.g. `view employees`, `create employees`). No policy class references in the scanned controller snippets; likely centralized permission names.
- **Events:** `AttendancePunched` exists; no `EmployeeCreated`, `PayrollGenerated`, `LeaveApproved` etc.
- **Response format:** `response()->success()` / `response()->forbidden()` / `response()->error()` — reusable but not formally documented in a single contract.

### 1.4 Missing Backend Modules (No Tables/Services)

- Employee Documents (contracts, certificates, IDs, expiry tracking)
- Performance Management (KPIs, reviews, ratings, promotion)
- Recruitment / Onboarding (candidates, interviews, pipeline, checklist)
- Training & Certification (programs, employee certifications, skills)
- Asset Management (assets, assignment to employees, warehouse devices)
- Disciplinary (warnings, violations, suspensions)
- Expense & Reimbursement (employee claims, approval workflow, payroll integration)
- Salary Structure & Components (earnings/deductions, tax, formulas)
- Payroll Run / Payslip (structured run per month, entry per employee, component breakdown)
- Warehouse workforce analytics (employee-warehouse assignment, productivity metrics)
- Approval workflow engine (unified leave/attendance/expense/overtime approval queues)

---

## 2. Frontend (Next.js + Shadcn) — Current State

### 2.1 HRM Pages Present

- `app/(dashboard)/hrm/attendances/page.tsx`
- `app/(dashboard)/hrm/leaves/page.tsx`
- `app/(dashboard)/hrm/overtimes/page.tsx`
- `app/(dashboard)/hrm/employees/page.tsx`
- `app/(dashboard)/hrm/leave-types/page.tsx`
- `app/(dashboard)/hrm/shifts/page.tsx`
- `app/(dashboard)/hrm/designations/page.tsx`
- `app/(dashboard)/hrm/departments/page.tsx`
- `app/(dashboard)/hrm/holidays/page.tsx`

**Missing:** `app/(dashboard)/hrm/payroll/page.tsx` (sidebar links to `/hrm/payroll` but no page). No HR dashboard (overview widgets, heatmaps, approval queue). No employee 360° profile (tabs: Profile, Attendance, Leaves, Payroll, Performance, Assets, Documents).

### 2.2 Feature Structure

- Per-entity feature folders under `features/hrm/<entity>/`: `api.ts`, `types.ts`, `schemas.ts`, `constants.ts`, `components/` (client, table, columns, dialogs, provider, primary-buttons, import/export, empty-state, bulk actions). Pattern is consistent (e.g. employees, attendances, leaves, overtimes, shifts, designations, departments, leave-types, holidays).
- **Payroll:** No `features/hrm/payroll/` — sidebar points to payroll but no feature or page.

### 2.3 Data & API

- React Query + `useApiClient()`; key factories (e.g. `employeeKeys.list(params)`); mutations for CRUD, bulk, import, export, template download.
- API base paths align with backend (e.g. `/employees`, `/attendances`). Pagination and filters passed as params.
- No shared “HR dashboard” or “approval queue” API hooks.

### 2.4 UX Gaps

- No HR overview dashboard (workforce summary, attendance analytics, payroll snapshot, e-commerce productivity).
- No approval center (single screen for leave/attendance/expense/overtime approvals).
- No employee 360° profile page.
- Tables: need confirmation of server-side pagination, advanced filters, bulk actions, and CSV export across all entities (pattern exists; enterprise “advanced search” and analytics display can be enhanced).
- No role-based visibility documented beyond sidebar permissions.

---

## 3. Business Logic Relationships (Current)

- **Departments → Employees:** One-to-many. No hierarchy (parent/manager).
- **Designations → Employees:** One-to-many.
- **Shifts → Employees:** One-to-many (current shift). No history (effective_from/to).
- **Employees → Attendances, Leaves, Overtimes, Payrolls:** One-to-many. Employee has `user_id` (optional) and `basic_salary`; payroll generation uses attendance, leave, overtime, expense, sales commission.
- **Leave Types → Leaves:** One-to-many. Leave has approver_id; no multi-level workflow or balance.
- **Attendance:** Uses global checkin/checkout from `hrm_settings` for late/present; no shift or warehouse.
- **Payroll:** Single record per employee per month (or merged); `amount_array` holds salary/commission/expense/overtime; no structure/components or payroll run entity.
- **Warehouse:** `warehouses` table exists; `users` has `warehouse_id`; `expenses` has `warehouse_id` and optional `employee_id`. **Employees table has no `warehouse_id`** — warehouse workforce assignment is missing.
- **User ↔ Employee:** Optional 1:1; used for login and sales/commission. No self-service portal or employee-specific views.

---

## 4. E-Commerce / Warehouse Relevance

- **Present:** Sale agents (is_sale_agent, commission, sales_target), expenses tied to warehouse and optionally employee, payroll generation can filter by warehouse via `user.warehouse_id`.
- **Missing:** Explicit `employee → warehouse` assignment; shift allocation per warehouse; productivity metrics (orders picked/packed, delivery success, tickets resolved); incentive/commission engine beyond current sales target tiers; shift auto-allocation rules; attendance geo-fence.

---

## 5. Summary: Priority Gaps

### Critical (Must Have for Enterprise)

1. **Payroll architecture:** Introduce salary_structures, salary_components, payroll_runs, payroll_entries (and optional payroll_entry_items); payslip API; move from single amount + JSON to component-based calculation.
2. **Employee master:** employee_code (auto), employment_type, joining/confirmation/probation dates, reporting_manager_id, warehouse_id, work_location, salary_structure_id, employment_status; consider employee_profiles for sensitive PII.
3. **Attendance:** shift_id, late_minutes, early_exit_minutes, worked_hours, overtime_minutes, checkin_source, latitude/longitude; optional attendance_adjustments and approval workflow.
4. **Leave:** leave_balances, accrual/carry-forward/encashment execution, multi-level approval (or at least workflow state).
5. **Frontend:** Add payroll page and HR dashboard; add employee 360° profile.

### High (E-Commerce & Scale)

6. Warehouse workforce: employee warehouse assignment, shift per warehouse, productivity metrics.
7. Department hierarchy (parent_id, manager_id) for org chart.
8. Shift: break_duration, is_rotational, overtime_allowed; employee_shift_assignments for history/flex.
9. Approval center (backend APIs + frontend) for leave/attendance/expense/overtime.
10. Employee documents (with expiry) and expense reimbursement workflow.

### New Submodules (Tier 1–3)

11. Employee Documents, Performance Management, Recruitment/Onboarding, Training/Certification, Asset Management, Disciplinary, Expense Claims (HR-focused) — each with tables, services, API, and UI where applicable.
12. Events: EmployeeCreated, PayrollGenerated, LeaveApproved (and others) for notifications and auditing.
13. Reusable API response contract and query filters; caching for HR analytics.

---

## 6. Next Steps (Recommended Order)

1. **DB schema improvements:** Migrations for employee (and optional employee_profiles), attendance, leave_balances, salary_structures/components/structure_items, payroll_runs/entries/entry_items, department parent_id/manager_id, shift extensions, employee_documents, etc.
2. **Backend upgrades:** Models, relationships, and services for new/updated tables; payroll engine refactor; new endpoints (payslip, dashboard stats, approval queue); events; keep existing DocBlocks and patterns.
3. **Frontend:** Add payroll feature + page; HR dashboard; employee 360°; approval center; improve tables (filters, export, bulk).
4. **New modules:** Implement Tier-1 then Tier-2/3 submodules incrementally with the same patterns (Service, Form Request, Resource, Policy, API routes, then frontend feature + page).

This document should be used as the single source of truth for “what exists” and “what’s missing” before implementing schema changes and code.

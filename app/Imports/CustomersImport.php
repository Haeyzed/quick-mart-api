<?php

declare(strict_types=1);

namespace App\Imports;

use App\Enums\DiscountPlanTypeEnum;
use App\Models\Customer;
use App\Models\CustomerGroup;
use App\Models\Deposit;
use App\Models\DiscountPlan;
use App\Models\DiscountPlanCustomer;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;

/**
 * Excel/CSV import for Customer entities.
 * Same pattern as BillersImport: WithChunkReading for memory efficiency.
 * Assigns generic discount plans and creates Deposit when deposit > 0.
 */
class CustomersImport implements OnEachRow, SkipsEmptyRows, WithHeadingRow, WithValidation, WithChunkReading
{
    public function onRow(Row $row): void
    {
        $data = $row->toArray();

        $name = trim((string)($data['name'] ?? ''));
        if ($name === '') {
            return;
        }

        $customerGroupName = trim((string)($data['customer_group'] ?? $data['customergroup'] ?? ''));
        $customerGroupId = null;
        if ($customerGroupName !== '') {
            $group = CustomerGroup::where('name', $customerGroupName)->where('is_active', true)->first();
            $customerGroupId = $group?->id;
        }

        $phoneNumber = trim((string)($data['phone_number'] ?? $data['phonenumber'] ?? ''));
        $attrs = [
            'customer_group_id' => $customerGroupId,
            'name' => $name,
            'company_name' => trim((string)($data['company_name'] ?? $data['companyname'] ?? '')) ?: null,
            'email' => trim((string)($data['email'] ?? '')) ?: null,
            'phone_number' => $phoneNumber ?: null,
            'address' => trim((string)($data['address'] ?? '')) ?: null,
            'city' => trim((string)($data['city'] ?? '')) ?: null,
            'state' => trim((string)($data['state'] ?? '')) ?: null,
            'postal_code' => trim((string)($data['postal_code'] ?? $data['postalcode'] ?? '')) ?: null,
            'country' => trim((string)($data['country'] ?? '')) ?: null,
            'deposit' => (float)($data['deposit'] ?? 0),
            'is_active' => true,
        ];

        $customer = $phoneNumber !== ''
            ? Customer::updateOrCreate(['phone_number' => $phoneNumber], $attrs)
            : Customer::create($attrs);

        $genericPlans = DiscountPlan::where('is_active', true)->where('type', DiscountPlanTypeEnum::GENERIC->value)->get();
        foreach ($genericPlans as $plan) {
            DiscountPlanCustomer::firstOrCreate(
                ['discount_plan_id' => $plan->id, 'customer_id' => $customer->id]
            );
        }

        if ($customer->deposit > 0 && Auth::id()) {
            Deposit::create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'amount' => $customer->deposit,
                'note' => 'Initial deposit from import',
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
        ];
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}

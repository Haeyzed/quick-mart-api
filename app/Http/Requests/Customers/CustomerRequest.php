<?php

declare(strict_types=1);

namespace App\Http\Requests\Customers;

use App\Http\Requests\BaseRequest;
use App\Models\Supplier;
use Illuminate\Validation\Rule;

/**
 * Form request for customer create and update validation.
 *
 * Validates name, customer_group_id, contact and address fields, opening_balance, deposit, etc.
 * Unique phone_number scoped to active customers only.
 * On update, ignores the current customer's linked supplier (when "both") and linked user (when "user") for uniqueness.
 * Conditional validation (from quick-mart-old):
 * - When "both" (customer + supplier): company_name and email required and unique in active suppliers; address, city required.
 * - When "user" (create login): username and email required and unique in users (not deleted); password required.
 */
class CustomerRequest extends BaseRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $customer = $this->route('customer');
        $customerId = $customer?->id;
        $isBoth = $this->boolean('both');
        $isUser = $this->boolean('user');

        $supplierIdToIgnore = null;
        $userIdToIgnore = null;
        if ($customer) {
            $supplierIdToIgnore = Supplier::query()
                ->where('email', $customer->email)
                ->where('company_name', $customer->company_name)
                ->where('is_active', true)
                ->value('id');
            $userIdToIgnore = $customer->user_id;
        }

        $rules = [
            'customer_group_id' => ['required', 'integer', 'exists:customer_groups,id'],
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'type' => ['nullable', 'string', 'max:50'],
            'phone_number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('customers', 'phone_number')->ignore($customerId)->where('is_active', true),
            ],
            'wa_number' => ['nullable', 'string', 'max:255'],
            'tax_no' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:500'],
            'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            'state_id' => ['nullable', 'integer', 'exists:states,id'],
            'city_id' => ['nullable', 'integer', 'exists:cities,id'],
            'postal_code' => ['nullable', 'string', 'max:50'],
            'opening_balance' => ['nullable', 'numeric', 'min:0'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'deposit' => ['nullable', 'numeric', 'min:0'],
            'pay_term_no' => ['nullable', 'integer', 'min:0'],
            'pay_term_period' => ['nullable', 'string', 'max:50'],
            'is_active' => ['nullable', 'boolean'],
            'both' => ['nullable', 'boolean'],
            'user' => ['nullable', 'boolean'],
            'username' => ['nullable', 'string', 'max:255'],
            'password' => ['nullable', 'string', 'min:8', 'max:255'],
        ];

        if ($isBoth) {
            $rules['company_name'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('suppliers', 'company_name')->ignore($supplierIdToIgnore)->where('is_active', true),
            ];
            $emailRules = ['required', 'email', 'max:255', Rule::unique('suppliers', 'email')->ignore($supplierIdToIgnore)->where('is_active', true)];
            if ($isUser) {
                $emailRules[] = Rule::unique('users', 'email')->ignore($userIdToIgnore)->where(fn($q) => $q->where('is_deleted', false)->orWhereNull('is_deleted'));
            }
            $rules['email'] = $emailRules;
            $rules['address'] = ['required', 'string', 'max:500'];
            $rules['city_id'] = ['nullable', 'integer', 'exists:cities,id'];
        }
        if ($isUser) {
            $rules['username'] = [
                'required',
                'string',
                'max:255',
                Rule::unique('users', 'username')->ignore($userIdToIgnore)->where(fn($q) => $q->where('is_deleted', false)->orWhereNull('is_deleted')),
            ];
            if (!$isBoth) {
                $rules['email'] = [
                    'required',
                    'email',
                    'max:255',
                    Rule::unique('users', 'email')->ignore($userIdToIgnore)->where(fn($q) => $q->where('is_deleted', false)->orWhereNull('is_deleted')),
                ];
            }
            $rules['password'] = ['required', 'string', 'min:8', 'max:255'];
        }

        return $rules;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('is_active')) {
            $this->merge(['is_active' => filter_var($this->is_active, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('both')) {
            $this->merge(['both' => filter_var($this->both, FILTER_VALIDATE_BOOLEAN)]);
        }
        if ($this->has('user')) {
            $this->merge(['user' => filter_var($this->user, FILTER_VALIDATE_BOOLEAN)]);
        }
    }
}

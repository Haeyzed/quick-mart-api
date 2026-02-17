<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CustomerTypeEnum;
use App\Enums\DiscountPlanTypeEnum;
use App\Enums\RewardPointTypeEnum;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Models\Biller;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Deposit;
use App\Models\DiscountPlan;
use App\Models\DiscountPlanCustomer;
use App\Models\Payment;
use App\Models\Returns;
use App\Models\RewardPoint;
use App\Models\Roles;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class CustomerService
 * Handles business logic for Customers.
 */
class CustomerService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

    /**
     * Get paginated customers based on filters.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedCustomers(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Customer::query()
            ->with(['customerGroup:id,name', 'discountPlans:id,name'])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get list of customer options.
     * Returns value/label format for select/combobox components.
     *
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return Customer::active()
            ->select('id', 'name', 'company_name')
            ->orderBy('name')
            ->get()
            ->map(fn (Customer $customer) => [
                'value' => $customer->id,
                'label' => $customer->company_name ? "{$customer->name} ({$customer->company_name})" : $customer->name,
            ]);
    }

    /**
     * Retrieve a single customer by instance.
     */
    public function getCustomer(Customer $customer): Customer
    {
        return $customer->fresh(['customerGroup', 'discountPlans']);
    }

    /**
     * Create a new customer.
     * Assigns generic discount plans and creates Deposit when deposit > 0.
     * When "user" is true, creates a User and links customer; when "both" is true, creates a Supplier with same details.
     *
     * @param  array<string, mixed>  $data
     */
    public function createCustomer(array $data): Customer
    {
        return DB::transaction(function () use ($data) {
            $createSupplier = !empty($data['both']);
            $data['is_active'] = $data['is_active'] ?? true;
            if (empty($data['type'])) {
                $data['type'] = CustomerTypeEnum::REGULAR->value;
            }

            if (!empty($data['user'])) {
                $user = $this->createUserForCustomer($data);
                $data['user_id'] = $user->id;
            }
            unset($data['user'], $data['username'], $data['password'], $data['both']);

            $customer = Customer::create($this->onlyFillableCustomerData($data));

            if ($createSupplier) {
                $this->createSupplierFromCustomerData(array_merge($customer->getAttributes(), $data));
            }

            $this->assignGenericDiscountPlans($customer);
            $this->createInitialDepositIfNeeded($customer);
            $this->createOpeningBalanceSaleIfNeeded($customer, $data);
            $this->syncCustomFieldsForCustomer($customer, $data);

            return $customer->fresh(['customerGroup', 'discountPlans']);
        });
    }

    /**
     * Create a User for customer login (phone from phone_number, role = Customer).
     *
     * @param array<string, mixed> $data
     */
    private function createUserForCustomer(array $data): User
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new RuntimeException('Authenticated user required to create customer login.');
        }

        $customerRoleId = Roles::query()
            ->where('name', 'Customer')
            ->where('is_active', true)
            ->value('id');

        if (!$customerRoleId) {
            throw new RuntimeException('Customer role not found. Please ensure a role named "Customer" exists and is active.');
        }

        return User::create([
            'name' => $data['name'],
            'username' => $data['username'] ?? $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'phone' => $data['phone_number'] ?? null,
            'role_id' => $customerRoleId,
            'is_active' => true,
            'is_deleted' => false,
        ]);
    }

    /**
     * Keep only keys that are fillable on Customer model (exclude custom field and request-only keys).
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function onlyFillableCustomerData(array $data): array
    {
        $fillable = (new Customer)->getFillable();

        return array_intersect_key($data, array_flip($fillable));
    }

    /**
     * Create a Supplier with same details as customer (quick-mart-old "both").
     *
     * @param array<string, mixed> $data
     */
    private function createSupplierFromCustomerData(array $data): void
    {
        Supplier::create([
            'name' => $data['name'] ?? '',
            'company_name' => $data['company_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'wa_number' => $data['wa_number'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'country' => $data['country'] ?? null,
            'is_active' => true,
        ]);
    }

    private function assignGenericDiscountPlans(Customer $customer): void
    {
        $genericPlans = DiscountPlan::query()
            ->where('is_active', true)
            ->where('type', DiscountPlanTypeEnum::GENERIC->value)
            ->get();

        foreach ($genericPlans as $plan) {
            DiscountPlanCustomer::firstOrCreate(
                [
                    'discount_plan_id' => $plan->id,
                    'customer_id' => $customer->id,
                ]
            );
        }
    }

    private function createInitialDepositIfNeeded(Customer $customer): void
    {
        $amount = (float)($customer->deposit ?? 0);
        if ($amount <= 0) {
            return;
        }

        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        Deposit::create([
            'customer_id' => $customer->id,
            'user_id' => $userId,
            'amount' => $amount,
        ]);
    }

    /**
     * Create a dummy "Opening balance" sale when customer has opening_balance > 0 (quick-mart-old behaviour).
     */
    private function createOpeningBalanceSaleIfNeeded(Customer $customer, array $data): void
    {
        $openingBalance = (float)($data['opening_balance'] ?? $customer->opening_balance ?? 0);
        if ($openingBalance <= 0) {
            return;
        }

        $userId = Auth::id();
        if (!$userId) {
            return;
        }

        $warehouseId = (int)(Warehouse::query()->value('id') ?? 1);
        $billerId = (int)(Biller::query()->where('is_active', true)->value('id') ?? 1);

        Sale::create([
            'reference_no' => 'cob-' . date('Ymd') . '-' . date('his'),
            'customer_id' => $customer->id,
            'user_id' => $userId,
            'warehouse_id' => $warehouseId,
            'biller_id' => $billerId,
            'item' => 0,
            'total_qty' => 0,
            'total_discount' => 0,
            'total_tax' => 0,
            'total_price' => $openingBalance,
            'grand_total' => $openingBalance,
            'paid_amount' => 0,
            'sale_status' => 'completed',
            'payment_status' => 'pending',
            'sale_type' => 'Opening balance',
        ]);
    }

    /**
     * Persist custom field values onto customer (quick-mart-old: custom fields stored on customers table).
     *
     * @param array<string, mixed> $data
     */
    private function syncCustomFieldsForCustomer(Customer $customer, array $data): void
    {
        $allowedColumns = $this->getCustomerCustomFieldColumnNames();
        if (empty($allowedColumns)) {
            return;
        }

        $customFieldData = [];
        foreach ($allowedColumns as $col) {
            if (array_key_exists($col, $data)) {
                $value = $data[$col];
                if (is_array($value)) {
                    $value = implode(',', $value);
                }
                $customFieldData[$col] = $value;
            }
        }

        if ($customFieldData !== []) {
            DB::table('customers')->where('id', $customer->id)->update($customFieldData);
        }
    }

    /**
     * Get custom field column names for customer that exist on the customers table (quick-mart-old).
     * Public so the controller can merge request input for create/update.
     *
     * @return array<int, string>
     */
    public function getCustomerCustomFieldColumnNames(): array
    {
        $columns = Schema::getColumnListing('customers');
        $customFields = CustomField::query()
            ->where('belongs_to', 'customer')
            ->get();

        $result = [];
        foreach ($customFields as $field) {
            $col = str_replace(' ', '_', strtolower($field->name));
            if (in_array($col, $columns, true)) {
                $result[] = $col;
            }
        }

        return $result;
    }

    /**
     * Update an existing customer.
     *
     * @param  array<string, mixed>  $data
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        return DB::transaction(function () use ($customer, $data) {
            if (!empty($data['user']) && !$customer->user_id) {
                $user = $this->createUserForCustomer($data);
                $data['user_id'] = $user->id;
            }
            unset($data['user'], $data['username'], $data['password'], $data['both']);

            $customer->update($this->onlyFillableCustomerData($data));
            $this->syncCustomFieldsForCustomer($customer, $data);

            return $customer->fresh(['customerGroup', 'discountPlans']);
        });
    }

    /**
     * Bulk delete customers (soft: is_active = false).
     *
     * @param  array<int>  $ids
     * @return int Count of deleted items.
     */
    public function bulkDeleteCustomers(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            $count = 0;
            $customers = Customer::query()->whereIn('id', $ids)->get();
            foreach ($customers as $customer) {
                $this->deleteCustomer($customer);
                $count++;
            }

            return $count;
        });
    }

    /**
     * Update status for multiple customers.
     *
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return Customer::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }

    /**
     * Delete a customer (soft: is_active = false, remove generic discount plan links).
     */
    public function deleteCustomer(Customer $customer): void
    {
        DB::transaction(function () use ($customer) {
            $genericPlanIds = DiscountPlan::query()
                ->where('is_active', true)
                ->where('type', DiscountPlanTypeEnum::GENERIC->value)
                ->pluck('id');

            DiscountPlanCustomer::where('customer_id', $customer->id)
                ->whereIn('discount_plan_id', $genericPlanIds)
                ->delete();

            $customer->update(['is_active' => false]);
        });
    }

    /**
     * Import customers from file.
     */
    public function importCustomers(UploadedFile $file): void
    {
        ExcelFacade::import(new CustomersImport, $file);
    }

    /**
     * Download a customers CSV template.
     */
    public function download(): string
    {
        $fileName = 'customers-sample.csv';

        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template customers not found.');
        }

        return $path;
    }

    /**
     * Export customers to file.
     *
     * @param  array<int>  $ids
     * @param  string  $format  'excel' or 'pdf'
     * @param  array<string>  $columns
     * @param  array{start_date?: string, end_date?: string}  $filters  Optional date filters for created_at
     * @return string Relative file path.
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'customers_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new CustomersExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }

    /**
     * Get customer balance summary (opening_balance, total_sales, total_paid, total_returns, balance_due).
     *
     * @return array{opening_balance: float, total_sales: float, total_paid: float, total_returns: float, balance_due: float}
     */
    public function getCustomerSummary(Customer $customer): array
    {
        $openingBalance = (float)($customer->opening_balance ?? 0);
        $sales = Sale::query()
            ->where('customer_id', $customer->id)
            ->whereNull('deleted_at')
            ->get();
        $totalSales = $sales->sum('grand_total');
        $totalPaid = $sales->sum(fn($s) => Payment::where('sale_id', $s->id)->sum('amount'));
        $totalReturns = (float)Returns::where('customer_id', $customer->id)->sum('grand_total');
        $balanceDue = $totalSales - ($openingBalance + $totalPaid + $totalReturns);

        return [
            'opening_balance' => $openingBalance,
            'total_sales' => round($totalSales, 2),
            'total_paid' => round($totalPaid, 2),
            'total_returns' => round($totalReturns, 2),
            'balance_due' => round($balanceDue, 2),
        ];
    }

    /**
     * Get customer ledger (sales as debit, payments and returns as credit) with running balance.
     *
     * @return array<int, array{date: string, type: string, reference: string, debit: float, credit: float, balance: string}>
     */
    public function getCustomerLedger(Customer $customer): array
    {
        $sales = Sale::query()
            ->where('customer_id', $customer->id)
            ->whereNull('deleted_at')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'date' => $s->created_at->format('Y-m-d'),
                'type' => 'Sale',
                'reference' => $s->reference_no,
                'debit' => (float)$s->grand_total,
                'credit' => 0.0,
            ]);

        $payments = collect();
        foreach ($sales as $row) {
            $salePayments = Payment::query()
                ->where('sale_id', $row['id'])
                ->get()
                ->map(fn($p) => [
                    'id' => $p->id,
                    'date' => ($p->payment_at ?? $p->created_at)?->format('Y-m-d') ?? '-',
                    'type' => 'Payment',
                    'reference' => $p->payment_reference ?? '-',
                    'debit' => 0.0,
                    'credit' => (float)$p->amount,
                ]);
            $payments = $payments->merge($salePayments);
        }

        $returns = Returns::query()
            ->where('customer_id', $customer->id)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'date' => $r->created_at->format('Y-m-d'),
                'type' => 'Purchase Return',
                'reference' => $r->reference_no,
                'debit' => 0.0,
                'credit' => (float)$r->grand_total,
            ]);

        $ledger = $sales->merge($payments)->merge($returns)->sortBy('date')->values();
        $balance = 0.0;
        $result = [];
        foreach ($ledger as $row) {
            $balance += $row['debit'] - $row['credit'];
            $result[] = [
                'date' => $row['date'],
                'type' => $row['type'],
                'reference' => $row['reference'],
                'debit' => $row['debit'],
                'credit' => $row['credit'],
                'balance' => number_format($balance, 2),
            ];
        }

        return $result;
    }

    /**
     * Get deposits for a customer.
     *
     * @return Collection<int, Deposit>
     */
    public function getCustomerDeposits(Customer $customer): Collection
    {
        return $customer->deposits()->with('user')->orderByDesc('created_at')->get();
    }

    /**
     * Add a deposit for a customer. Updates customer.deposit and creates Deposit.
     */
    public function addDeposit(Customer $customer, float $amount, ?string $note = null): Deposit
    {
        $userId = Auth::id();
        if (!$userId) {
            throw new RuntimeException('Authenticated user required to add deposit.');
        }

        return DB::transaction(function () use ($customer, $amount, $note, $userId) {
            $customer->increment('deposit', $amount);

            return Deposit::create([
                'customer_id' => $customer->id,
                'user_id' => $userId,
                'amount' => $amount,
                'note' => $note,
            ]);
        });
    }

    /**
     * Update a deposit and adjust customer deposit total.
     */
    public function updateDeposit(Deposit $deposit, float $amount, ?string $note = null): Deposit
    {
        return DB::transaction(function () use ($deposit, $amount, $note) {
            $diff = $amount - $deposit->amount;
            $deposit->customer->increment('deposit', $diff);
            $deposit->update(['amount' => $amount, 'note' => $note]);

            return $deposit->fresh();
        });
    }

    /**
     * Delete a deposit and decrease customer deposit.
     */
    public function deleteDeposit(Deposit $deposit): void
    {
        DB::transaction(function () use ($deposit) {
            $deposit->customer->decrement('deposit', $deposit->amount);
            $deposit->delete();
        });
    }

    /**
     * Get reward points for a customer.
     *
     * @return Collection<int, RewardPoint>
     */
    public function getCustomerPoints(Customer $customer): Collection
    {
        return $customer->rewardPoints()->with('creator')->orderByDesc('created_at')->get();
    }

    /**
     * Add reward points to a customer (manual).
     */
    public function addPoint(Customer $customer, float $points, ?string $note = null): RewardPoint
    {
        $userId = Auth::id();

        return DB::transaction(function () use ($customer, $points, $note, $userId) {
            $point = RewardPoint::create([
                'customer_id' => $customer->id,
                'reward_point_type' => RewardPointTypeEnum::MANUAL->value,
                'points' => $points,
                'deducted_points' => 0,
                'note' => $note,
                'created_by' => $userId,
            ]);
            $customer->increment('points', $points);

            return $point->load('creator');
        });
    }

    /**
     * Update a reward point entry and adjust customer points.
     */
    public function updatePoint(RewardPoint $point, float $points, ?string $note = null): RewardPoint
    {
        return DB::transaction(function () use ($point, $points, $note) {
            $customer = $point->customer;
            $customer->decrement('points', $point->points);
            $customer->increment('points', $points);
            $updates = ['points' => $points];
            if ($note !== null) {
                $updates['note'] = $note;
            }
            $point->update($updates);

            return $point->fresh('creator');
        });
    }

    /**
     * Delete a reward point entry and decrease customer points.
     */
    public function deletePoint(RewardPoint $point): void
    {
        DB::transaction(function () use ($point) {
            $point->customer->decrement('points', $point->points);
            $point->delete();
        });
    }

    /**
     * Get payments for a customer (all payments linked to their sales).
     *
     * @return Collection<int, Payment>
     */
    public function getCustomerPayments(Customer $customer): Collection
    {
        return Payment::query()
            ->whereHas('sale', fn($q) => $q->where('customer_id', $customer->id)->whereNull('deleted_at'))
            ->with('sale')
            ->latest('created_at')
            ->get();
    }
}

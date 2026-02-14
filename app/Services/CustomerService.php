<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CustomerTypeEnum;
use App\Enums\DiscountPlanTypeEnum;
use App\Enums\RewardPointTypeEnum;
use App\Exports\CustomersExport;
use App\Imports\CustomersImport;
use App\Mail\ExportMail;
use App\Models\Biller;
use App\Models\Customer;
use App\Models\CustomField;
use App\Models\Deposit;
use App\Models\DiscountPlan;
use App\Models\DiscountPlanCustomer;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Payment;
use App\Models\Returns;
use App\Models\RewardPoint;
use App\Models\Roles;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

/**
 * Service class for Customer entity lifecycle operations.
 *
 * Centralizes business logic for Customer CRUD, bulk destroy, import/export,
 * summary/ledger, deposits, reward points, and payments (from quick-mart-old).
 * Delegates permission checks to CheckPermissionsTrait.
 */
class CustomerService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * Retrieve a single customer by instance.
     *
     * Requires customers-index permission.
     */
    public function getCustomer(Customer $customer): Customer
    {
        $this->requirePermission('customers-index');

        return $customer->fresh(['customerGroup', 'discountPlans']);
    }

    /**
     * Retrieve customers with optional filters and pagination.
     *
     * Supports status, customer_group_id, and search. Requires customers-index permission.
     *
     * @param array<string, mixed> $filters
     * @return LengthAwarePaginator<Customer>
     */
    public function getCustomers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('customers-index');

        return Customer::query()
            ->with(['customerGroup', 'discountPlans'])
            ->when(isset($filters['status']), fn($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when(isset($filters['customer_group_id']), fn($q) => $q->where('customer_group_id', $filters['customer_group_id']))
            ->when(!empty($filters['search']), function ($q) use ($filters) {
                $term = '%' . $filters['search'] . '%';
                $q->where(fn($subQ) => $subQ
                    ->where('name', 'like', $term)
                    ->orWhere('company_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                );
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Create a new customer.
     *
     * Assigns generic discount plans and creates Deposit when deposit > 0 (quick-mart-old logic).
     * When "user" is true, creates a User and links customer; when "both" is true, creates a Supplier with same details.
     * Defaults type to REGULAR when not provided. Requires customers-create permission.
     *
     * @param array<string, mixed> $data
     */
    public function createCustomer(array $data): Customer
    {
        $this->requirePermission('customers-create');

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
     * When "user" is true and customer has no user_id, creates a User and links (quick-mart-old).
     * Requires customers-update permission.
     *
     * @param array<string, mixed> $data
     */
    public function updateCustomer(Customer $customer, array $data): Customer
    {
        $this->requirePermission('customers-update');

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
     * @return \Illuminate\Database\Eloquent\Collection<int, Customer>
     */
    public function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        $this->requirePermission('customers-index');

        return Customer::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Bulk soft-delete customers. Requires customers-delete permission.
     *
     * @param array<int> $ids
     */
    public function bulkDeleteCustomers(array $ids): int
    {
        $this->requirePermission('customers-delete');

        $count = 0;
        foreach ($ids as $id) {
            $customer = Customer::find($id);
            if ($customer) {
                $this->deleteCustomer($customer);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Soft-delete a customer (is_active = false) and remove generic discount plan links.
     *
     * Requires customers-delete permission.
     */
    public function deleteCustomer(Customer $customer): void
    {
        $this->requirePermission('customers-delete');

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
     * Import customers from Excel/CSV. Requires customers-import permission.
     */
    public function importCustomers(UploadedFile $file): void
    {
        $this->requirePermission('customers-import');
        Excel::import(new CustomersImport, $file);
    }

    /**
     * Export customers to Excel or PDF. Supports download or email.
     *
     * Requires customers-export permission.
     *
     * @param array<int> $ids
     * @param array<string> $columns
     */
    public function exportCustomers(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('customers-export');

        $fileName = 'customers_' . now()->timestamp . '.' . ($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new CustomersExport($ids, $columns), $relativePath, 'public');
        } else {
            $customers = Customer::query()
                ->with('customerGroup')
                ->when(!empty($ids), fn($q) => $q->whereIn('id', $ids))
                ->orderBy('name')
                ->get();

            $pdf = PDF::loadView('exports.customers-pdf', compact('customers', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName);
        }

        return $relativePath;
    }

    private function sendExportEmail(User $user, string $path, string $fileName): void
    {
        $mailSetting = MailSetting::default()->firstOr(
            fn() => throw new RuntimeException('Mail settings are not configured.')
        );
        $generalSetting = GeneralSetting::latest()->first();
        $this->setMailInfo($mailSetting);
        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, 'Customers List', $generalSetting)
        );
    }

    /**
     * Get customer balance summary (opening_balance, total_sales, total_paid, total_returns, balance_due).
     * Requires customers-index permission.
     *
     * @return array{opening_balance: float, total_sales: float, total_paid: float, total_returns: float, balance_due: float}
     */
    public function getCustomerSummary(Customer $customer): array
    {
        $this->requirePermission('customers-index');

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
     * Requires customers-index permission.
     *
     * @return array<int, array{date: string, type: string, reference: string, debit: float, credit: float, balance: string}>
     */
    public function getCustomerLedger(Customer $customer): array
    {
        $this->requirePermission('customers-index');

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
     * Get deposits for a customer. Requires customers-index permission.
     *
     * @return Collection<int, Deposit>
     */
    public function getCustomerDeposits(Customer $customer): Collection
    {
        $this->requirePermission('customers-index');

        return $customer->deposits()->with('user')->orderByDesc('created_at')->get();
    }

    /**
     * Add a deposit for a customer. Updates customer.deposit and creates Deposit. Requires customers-update (or a dedicated permission if added).
     */
    public function addDeposit(Customer $customer, float $amount, ?string $note = null): Deposit
    {
        $this->requirePermission('customers-update');

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
        $this->requirePermission('customers-update');

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
        $this->requirePermission('customers-update');

        DB::transaction(function () use ($deposit) {
            $deposit->customer->decrement('deposit', $deposit->amount);
            $deposit->delete();
        });
    }

    /**
     * Get reward points for a customer. Requires customers-index permission.
     *
     * @return Collection<int, RewardPoint>
     */
    public function getCustomerPoints(Customer $customer): Collection
    {
        $this->requirePermission('customers-index');

        return $customer->rewardPoints()->with('creator')->orderByDesc('created_at')->get();
    }

    /**
     * Add reward points to a customer (manual). Requires customers-update.
     */
    public function addPoint(Customer $customer, float $points, ?string $note = null): RewardPoint
    {
        $this->requirePermission('customers-update');

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
        $this->requirePermission('customers-update');

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
        $this->requirePermission('customers-update');

        DB::transaction(function () use ($point) {
            $point->customer->decrement('points', $point->points);
            $point->delete();
        });
    }

    /**
     * Get payments for a customer (all payments linked to their sales). Requires customers-index permission.
     *
     * @return Collection<int, Payment>
     */
    public function getCustomerPayments(Customer $customer): Collection
    {
        $this->requirePermission('customers-index');

        return Payment::query()
            ->whereHas('sale', fn($q) => $q->where('customer_id', $customer->id)->whereNull('deleted_at'))
            ->with('sale')
            ->latest('created_at')
            ->get();
    }
}

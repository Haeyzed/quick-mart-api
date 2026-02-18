<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\PaymentStatusEnum;
use App\Enums\PurchaseStatusEnum;
use App\Exports\SuppliersExport;
use App\Imports\SuppliersImport;
use App\Mail\ExportMail;
use App\Models\Account;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Payment;
use App\Models\Purchase;
use App\Models\ReturnPurchase;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

/**
 * Service class for Supplier CRUD, listing, and accounting operations.
 *
 * Follows the same pattern as BillerService: index with filters, create/update with
 * optional image upload, soft delete via is_active. Also handles opening balance
 * purchase, ledger, balance due, payments, and clear due.
 */
class SupplierService extends BaseService
{
    use MailInfo;

    private const DEFAULT_SUPPLIER_IMAGES_PATH = 'images/supplier';

    private const TEMPLATE_PATH = 'Imports/Templates';

    public function __construct(
        private readonly UploadService $uploadService
    ) {
    }

    public function getSupplier(Supplier $supplier): Supplier
    {
        return $supplier->fresh(['country', 'state', 'city']);
    }

    /**
     * Create a supplier from customer data (e.g. when "also register as supplier" is checked).
     * Uses country_id, state_id, city_id directly like Supplier model (same pattern as Customer).
     * Does not check permissions; caller (e.g. CustomerService) is responsible for authorization.
     *
     * @param  array<string, mixed>  $data  Customer-style attributes (country_id, state_id, city_id)
     */
    public function createFromCustomerData(array $data): Supplier
    {
        $payload = [
            'name' => $data['name'] ?? '',
            'company_name' => $data['company_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone_number' => $data['phone_number'] ?? null,
            'wa_number' => $data['wa_number'] ?? null,
            'address' => $data['address'] ?? null,
            'country_id' => $data['country_id'] ?? null,
            'state_id' => $data['state_id'] ?? null,
            'city_id' => $data['city_id'] ?? null,
            'postal_code' => $data['postal_code'] ?? null,
            'is_active' => true,
        ];

        return Supplier::create($payload);
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Supplier>
     */
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Supplier>
     */
    public function getSuppliers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Supplier::query()
            ->with([
                'country:id,name',
                'state:id,name',
                'city:id,name',
            ])
            ->filter($filters)
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param array<string, mixed> $data
     */
    /**
     * Get list of supplier options for select/combobox.
     *
     * @return \Illuminate\Support\Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): \Illuminate\Support\Collection
    {
        return Supplier::query()
            ->where('is_active', true)
            ->select('id', 'name', 'company_name')
            ->orderBy('name')
            ->get()
            ->map(fn (Supplier $s) => [
                'value' => $s->id,
                'label' => $s->company_name ? "{$s->name} ({$s->company_name})" : $s->name,
            ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createSupplier(array $data): Supplier
    {
        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data = $this->handleImageUpload($data);
            }
            $data['is_active'] = $data['is_active'] ?? true;

            $supplier = Supplier::create($data);

            $openingBalance = (float)($data['opening_balance'] ?? 0);
            if ($openingBalance > 0) {
                $this->createOpeningBalancePurchase($supplier, $openingBalance);
            }

            return $supplier->fresh();
        });
    }

    /**
     * Process image upload and merge path into supplier data.
     *
     * @param array<string, mixed> $data Input data containing 'image' as UploadedFile.
     * @return array<string, mixed> Data with 'image' (path) set.
     */
    private function handleImageUpload(array $data): array
    {
        $path = $this->uploadService->upload(
            $data['image'],
            config('storage.suppliers.images', self::DEFAULT_SUPPLIER_IMAGES_PATH)
        );

        $data['image'] = $path;

        return $data;
    }

    /**
     * Create a dummy purchase for opening balance (amount owed to supplier).
     */
    private function createOpeningBalancePurchase(Supplier $supplier, float $amount): Purchase
    {
        $warehouse = Warehouse::query()->first();
        if (!$warehouse) {
            throw new RuntimeException('No warehouse found. Please create a warehouse first.');
        }

        return Purchase::create([
            'reference_no' => 'sob-' . now()->format('Ymd') . '-' . now()->format('his'),
            'supplier_id' => $supplier->id,
            'user_id' => auth()->id(),
            'warehouse_id' => $warehouse->id,
            'item' => 0,
            'total_qty' => 0,
            'total_discount' => 0,
            'total_tax' => 0,
            'total_cost' => $amount,
            'grand_total' => $amount,
            'paid_amount' => 0,
            'status' => PurchaseStatusEnum::COMPLETED->value,
            'payment_status' => PaymentStatusEnum::UNPAID->value,
            'purchase_type' => 'Opening balance',
        ]);
    }

    /**
     * @param array<string, mixed> $data
     */
    /**
     * @param array<string, mixed> $data
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        return DB::transaction(function () use ($supplier, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($supplier->image) {
                    $this->uploadService->delete($supplier->image);
                }
                $data = $this->handleImageUpload($data);
            }

            $supplier->update($data);

            return $supplier->fresh();
        });
    }

    /**
     * @return Collection<int, Supplier>
     */
    /**
     * @return Collection<int, Supplier>
     */
    public function getAllActive(): Collection
    {
        return Supplier::query()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * @param array<int> $ids
     */
    public function bulkDeleteSuppliers(array $ids): int
    {
        $count = 0;
        foreach ($ids as $id) {
            $supplier = Supplier::find($id);
            if ($supplier) {
                $this->deleteSupplier($supplier);
                $count++;
            }
        }

        return $count;
    }

    public function deleteSupplier(Supplier $supplier): void
    {
        DB::transaction(function () use ($supplier) {
            if ($supplier->image) {
                $this->uploadService->delete($supplier->image);
            }
            $supplier->update(['is_active' => false]);
        });
    }

    public function bulkActivateSuppliers(array $ids): int
    {
        return Supplier::whereIn('id', $ids)->update(['is_active' => true]);
    }

    public function bulkDeactivateSuppliers(array $ids): int
    {
        return Supplier::whereIn('id', $ids)->update(['is_active' => false]);
    }

    public function importSuppliers(UploadedFile $file): void
    {
        Excel::import(new SuppliersImport, $file);
    }

    /**
     * Download suppliers import template path.
     */
    public function download(): string
    {
        $fileName = 'suppliers-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! \Illuminate\Support\Facades\File::exists($path)) {
            throw new RuntimeException('Supplier import template not found.');
        }

        return $path;
    }

    /**
     * Generate export file and return relative path.
     *
     * @param  array<int>  $ids
     * @param  array<string>  $columns
     * @param  array{start_date?: string, end_date?: string}  $filters
     */
    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'suppliers_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/'.$fileName;

        if ($format === 'pdf') {
            $suppliers = Supplier::query()
                ->with(['country:id,name', 'state:id,name', 'city:id,name'])
                ->when(! empty($ids), fn ($q) => $q->whereIn('id', $ids))
                ->orderBy('company_name')
                ->get();
            $pdf = PDF::loadView('exports.suppliers-pdf', compact('suppliers', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        } else {
            Excel::store(new SuppliersExport($ids, $columns), $relativePath, 'public');
        }

        return $relativePath;
    }

    /**
     * @param array<int> $ids
     * @param array<string> $columns
     * @deprecated Use generateExportFile() and handle email in controller
     */
    public function exportSuppliers(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $fileName = 'suppliers_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/' . $fileName;

        if ($format === 'excel') {
            Excel::store(new SuppliersExport($ids, $columns), $relativePath, 'public');
        } else {
            $suppliers = Supplier::query()
                ->when(!empty($ids), fn($q) => $q->whereIn('id', $ids))
                ->orderBy('company_name')
                ->get();

            $pdf = PDF::loadView('exports.suppliers-pdf', compact('suppliers', 'columns'));
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
            new ExportMail($user, $path, $fileName, 'Suppliers List', $generalSetting, $mailSetting)
        );
    }

    /**
     * Get supplier ledger (purchases, payments, returns) sorted by date.
     *
     * @return array<int, array<string, mixed>>
     */
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getLedger(Supplier $supplier): array
    {
        $purchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->get()
            ->map(fn(Purchase $p) => [
                'id' => $p->id,
                'date' => $p->created_at?->format('Y-m-d'),
                'type' => 'Purchase',
                'reference' => $p->reference_no,
                'debit' => (float)$p->grand_total,
                'credit' => 0.0,
            ]);

        $payments = collect();
        foreach (Purchase::where('supplier_id', $supplier->id)->get() as $purchase) {
            foreach (Payment::where('purchase_id', $purchase->id)->get() as $payment) {
                $payments->push([
                    'id' => $payment->id,
                    'date' => ($payment->payment_at ?? $payment->created_at)?->format('Y-m-d'),
                    'type' => 'Payment',
                    'reference' => $payment->payment_reference ?? '-',
                    'debit' => 0.0,
                    'credit' => (float)$payment->amount,
                ]);
            }
        }

        $returns = ReturnPurchase::where('supplier_id', $supplier->id)
            ->get()
            ->map(fn($r) => [
                'id' => $r->id,
                'date' => $r->created_at?->format('Y-m-d'),
                'type' => 'Purchase Return',
                'reference' => $r->reference_no,
                'debit' => 0.0,
                'credit' => (float)$r->grand_total,
            ]);

        $ledger = $purchases->merge($payments)->merge($returns)
            ->sortBy('date')
            ->values()
            ->toArray();

        $balance = 0.0;
        foreach ($ledger as $key => $row) {
            $balance += ($row['debit'] - $row['credit']);
            $ledger[$key]['balance'] = number_format($balance, 2);
        }

        return $ledger;
    }

    /**
     * Get total balance due for supplier.
     */
    public function getBalanceDue(Supplier $supplier): float
    {
        $openingBalance = (float)($supplier->opening_balance ?? 0);
        $totalPurchases = (float)Purchase::where('supplier_id', $supplier->id)->sum('grand_total');
        $totalPaid = (float)DB::table('payments')
            ->join('purchases', 'payments.purchase_id', '=', 'purchases.id')
            ->where('purchases.supplier_id', $supplier->id)
            ->whereNull('purchases.deleted_at')
            ->sum('payments.amount');
        $totalReturns = (float)ReturnPurchase::where('supplier_id', $supplier->id)->sum('grand_total');

        return max(0, $openingBalance + $totalPurchases - $totalReturns - $totalPaid);
    }

    /**
     * Get supplier payment history.
     *
     * @return array<int, array<string, mixed>>
     */
    /**
     * @return array<int, array<string, mixed>>
     */
    public function getPayments(Supplier $supplier): array
    {
        return DB::table('payments')
            ->join('purchases', 'payments.purchase_id', '=', 'purchases.id')
            ->where('purchases.supplier_id', $supplier->id)
            ->whereNull('purchases.deleted_at')
            ->select(
                'payments.id',
                'payments.created_at',
                'payments.payment_reference',
                'payments.amount',
                'payments.paying_method',
                'payments.payment_at'
            )
            ->orderByDesc('payments.created_at')
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'created_at' => $p->created_at ? date('Y-m-d', strtotime($p->created_at)) : '-',
                'payment_reference' => $p->payment_reference ?? '-',
                'amount' => number_format((float)$p->amount, 2),
                'paying_method' => ucfirst($p->paying_method ?? '-'),
                'payment_at' => $p->payment_at
                    ? date('Y-m-d H:i', strtotime($p->payment_at))
                    : ($p->created_at ? date('Y-m-d H:i', strtotime($p->created_at)) : '-'),
            ])
            ->values()
            ->toArray();
    }

    /**
     * Record payment against supplier's due purchases.
     */
    public function clearDue(int $supplierId, float $amount, ?string $note = null, ?int $cashRegisterId = null): void
    {
        $supplier = Supplier::findOrFail($supplierId);
        $duePurchases = Purchase::query()
            ->where('supplier_id', $supplier->id)
            ->where('payment_status', '!=', PaymentStatusEnum::PAID->value)
            ->orderBy('created_at')
            ->get();

        $account = Account::query()->where('is_default', true)->first();
        if (!$account) {
            throw new RuntimeException('No default account found. Please configure accounting.');
        }

        $remainingAmount = $amount;

        DB::transaction(function () use ($duePurchases, $remainingAmount, $account, $note, $cashRegisterId): void {
            foreach ($duePurchases as $purchase) {
                if ($remainingAmount <= 0) {
                    break;
                }

                $dueAmount = $purchase->grand_total - $purchase->paid_amount;

                if ($remainingAmount >= $dueAmount) {
                    $paidAmount = $dueAmount;
                    $paymentStatus = PaymentStatusEnum::PAID->value;
                } else {
                    $paidAmount = $remainingAmount;
                    $paymentStatus = PaymentStatusEnum::PARTIAL->value;
                }

                Payment::create([
                    'payment_reference' => 'ppr-' . now()->format('Ymd') . '-' . now()->format('his'),
                    'purchase_id' => $purchase->id,
                    'user_id' => auth()->id(),
                    'cash_register_id' => $cashRegisterId,
                    'account_id' => $account->id,
                    'amount' => $paidAmount,
                    'change' => 0,
                    'paying_method' => 'Cash',
                    'payment_note' => $note,
                ]);

                $purchase->paid_amount += $paidAmount;
                $purchase->payment_status = $paymentStatus;
                $purchase->save();

                $remainingAmount -= $paidAmount;
            }
        });
    }
}

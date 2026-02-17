<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\CustomerBulkActionRequest;
use App\Http\Requests\Customers\CustomerDepositRequest;
use App\Http\Requests\Customers\CustomerPointRequest;
use App\Http\Requests\Customers\StoreCustomerRequest;
use App\Http\Requests\Customers\UpdateCustomerRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\DepositResource;
use App\Http\Resources\RewardPointResource;
use App\Mail\ExportMail;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\RewardPoint;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class CustomerController
 *
 * API Controller for Customer CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to CustomerService.
 *
 * @group Customer Management
 */
class CustomerController extends Controller
{
    /**
     * CustomerController constructor.
     */
    public function __construct(
        private readonly CustomerService $service
    ) {}

    /**
     * Display a paginated listing of customers.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view customers')) {
            return response()->forbidden('Permission denied for viewing customers list.');
        }

        $customers = $this->service->getPaginatedCustomers(
            $request->all(),
            (int) $request->input('per_page', 10)
        );

        $customers->through(function (Customer $customer) {
            $summary = $this->service->getCustomerSummary($customer);
            $customer->setAttribute('total_due', $summary['balance_due']);

            return new CustomerResource($customer);
        });

        return response()->success($customers, 'Customers retrieved successfully');
    }

    /**
     * Get customer options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view customers')) {
            return response()->forbidden('Permission denied for viewing customers options.');
        }

        return response()->success($this->service->getOptions(), 'Customer options retrieved successfully');
    }

    /**
     * Store a newly created customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create customers')) {
            return response()->forbidden('Permission denied for create customer.');
        }

        $data = array_merge(
            $request->validated(),
            $request->only($this->service->getCustomerCustomFieldColumnNames())
        );
        $customer = $this->service->createCustomer($data);

        return response()->success(
            new CustomerResource($customer->load(['customerGroup', 'discountPlans'])),
            'Customer created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('view customer details')) {
            return response()->forbidden('Permission denied for view customer.');
        }

        $customer = $this->service->getCustomer($customer);

        return response()->success(
            new CustomerResource($customer),
            'Customer details retrieved successfully'
        );
    }

    /**
     * Update the specified customer.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for update customer.');
        }

        $data = array_merge(
            $request->validated(),
            $request->only($this->service->getCustomerCustomFieldColumnNames())
        );
        $updatedCustomer = $this->service->updateCustomer($customer, $data);

        return response()->success(
            new CustomerResource($updatedCustomer),
            'Customer updated successfully'
        );
    }

    /**
     * Remove the specified customer (soft delete).
     */
    public function destroy(Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('delete customers')) {
            return response()->forbidden('Permission denied for delete customer.');
        }

        $this->service->deleteCustomer($customer);

        return response()->success(null, 'Customer deleted successfully');
    }

    /**
     * Bulk delete customers.
     */
    public function bulkDestroy(CustomerBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete customers')) {
            return response()->forbidden('Permission denied for bulk delete customers.');
        }

        $count = $this->service->bulkDeleteCustomers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} customers"
        );
    }

    /**
     * Bulk activate customers.
     */
    public function bulkActivate(CustomerBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for bulk update customers.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} customers activated"
        );
    }

    /**
     * Bulk deactivate customers.
     */
    public function bulkDeactivate(CustomerBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for bulk update customers.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} customers deactivated"
        );
    }

    /**
     * Import customers from Excel/CSV.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import customers')) {
            return response()->forbidden('Permission denied for import customers.');
        }

        $this->service->importCustomers($request->file('file'));

        return response()->success(null, 'Customers imported successfully');
    }

    /**
     * Export customers to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export customers')) {
            return response()->forbidden('Permission denied for export customers.');
        }

        $validated = $request->validated();

        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (! $user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (! $mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'customers_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Customer Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: '.$user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download customers module import sample template.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import customers')) {
            return response()->forbidden('Permission denied for downloading customers import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }

    /**
     * Get customer balance summary (opening_balance, total_sales, total_paid, total_returns, balance_due).
     */
    public function summary(Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('view customers')) {
            return response()->forbidden('Permission denied for view customer.');
        }

        $data = $this->service->getCustomerSummary($customer);

        return response()->success($data, 'Customer summary retrieved successfully');
    }

    /**
     * Get customer ledger (sales debits, payments/returns credits) with running balance.
     */
    public function ledger(Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('view customers')) {
            return response()->forbidden('Permission denied for view customer.');
        }

        $data = $this->service->getCustomerLedger($customer);

        return response()->success(['data' => $data], 'Customer ledger retrieved successfully');
    }

    /**
     * List deposits for a customer.
     */
    public function deposits(Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('view customers')) {
            return response()->forbidden('Permission denied for view customer.');
        }

        $deposits = $this->service->getCustomerDeposits($customer);

        return response()->success(
            DepositResource::collection($deposits),
            'Customer deposits retrieved successfully'
        );
    }

    /**
     * Add a deposit for a customer.
     */
    public function storeDeposit(CustomerDepositRequest $request, Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for update customer.');
        }

        $validated = $request->validated();
        $deposit = $this->service->addDeposit(
            $customer,
            (float) $validated['amount'],
            $validated['note'] ?? null
        );

        return response()->success(
            new DepositResource($deposit->load('user')),
            'Deposit added successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update a customer deposit (must belong to customer).
     */
    public function updateDeposit(CustomerDepositRequest $request, Customer $customer, Deposit $deposit): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for update customer.');
        }

        if ($deposit->customer_id !== $customer->id) {
            abort(404, 'Deposit not found for this customer.');
        }

        $validated = $request->validated();
        $deposit = $this->service->updateDeposit(
            $deposit,
            (float) $validated['amount'],
            $validated['note'] ?? null
        );

        return response()->success(new DepositResource($deposit->load('user')), 'Deposit updated successfully');
    }

    /**
     * Delete a customer deposit.
     */
    public function destroyDeposit(Customer $customer, Deposit $deposit): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for update customer.');
        }

        if ($deposit->customer_id !== $customer->id) {
            abort(404, 'Deposit not found for this customer.');
        }

        $this->service->deleteDeposit($deposit);

        return response()->success(null, 'Deposit deleted successfully');
    }

    /**
     * List reward points for a customer.
     */
    public function points(Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('view customers')) {
            return response()->forbidden('Permission denied for view customer.');
        }

        $points = $this->service->getCustomerPoints($customer);

        return response()->success(
            RewardPointResource::collection($points),
            'Customer reward points retrieved successfully'
        );
    }

    /**
     * Add reward points to a customer.
     */
    public function storePoint(CustomerPointRequest $request, Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for update customer.');
        }

        $validated = $request->validated();
        $point = $this->service->addPoint(
            $customer,
            (float) $validated['points'],
            $validated['note'] ?? null
        );

        return response()->success(
            new RewardPointResource($point),
            'Reward points added successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update a customer reward point entry.
     */
    public function updatePoint(CustomerPointRequest $request, Customer $customer, RewardPoint $point): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for update customer.');
        }

        if ($point->customer_id !== $customer->id) {
            abort(404, 'Reward point not found for this customer.');
        }

        $validated = $request->validated();
        $point = $this->service->updatePoint(
            $point,
            (float) $validated['points'],
            $validated['note'] ?? null
        );

        return response()->success(new RewardPointResource($point), 'Reward point updated successfully');
    }

    /**
     * Delete a customer reward point entry.
     */
    public function destroyPoint(Customer $customer, RewardPoint $point): JsonResponse
    {
        if (auth()->user()->denies('update customers')) {
            return response()->forbidden('Permission denied for update customer.');
        }

        if ($point->customer_id !== $customer->id) {
            abort(404, 'Reward point not found for this customer.');
        }

        $this->service->deletePoint($point);

        return response()->success(null, 'Reward point deleted successfully');
    }

    /**
     * List payments for a customer (payments against their sales).
     */
    public function payments(Customer $customer): JsonResponse
    {
        if (auth()->user()->denies('view customers')) {
            return response()->forbidden('Permission denied for view customer.');
        }

        $payments = $this->service->getCustomerPayments($customer);
        $data = $payments->map(fn ($p) => [
            'id' => $p->id,
            'created_at' => $p->created_at?->format('Y-m-d'),
            'payment_reference' => $p->payment_reference ?? '-',
            'amount' => number_format((float) $p->amount, 2),
            'paying_method' => ucfirst($p->paying_method ?? '-'),
            'payment_at' => $p->payment_at
                ? $p->payment_at->format('Y-m-d H:i')
                : $p->created_at?->format('Y-m-d H:i'),
        ]);

        return response()->success(['data' => $data], 'Customer payments retrieved successfully');
    }
}

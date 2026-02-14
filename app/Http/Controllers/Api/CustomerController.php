<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customers\CustomerBulkDestroyRequest;
use App\Http\Requests\Customers\CustomerDepositRequest;
use App\Http\Requests\Customers\CustomerIndexRequest;
use App\Http\Requests\Customers\CustomerPointRequest;
use App\Http\Requests\Customers\CustomerRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\DepositResource;
use App\Http\Resources\RewardPointResource;
use App\Models\Customer;
use App\Models\Deposit;
use App\Models\RewardPoint;
use App\Models\User;
use App\Services\CustomerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * API Controller for Customer CRUD.
 *
 * @group Customer Management
 */
class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerService $service
    )
    {
    }

    public function index(CustomerIndexRequest $request): JsonResponse
    {
        $customers = $this->service->getCustomers(
            $request->validated(),
            (int)$request->input('per_page', 10)
        );

        $customers->through(function (Customer $customer) {
            $summary = $this->service->getCustomerSummary($customer);
            $customer->setAttribute('total_due', $summary['balance_due']);

            return new CustomerResource($customer);
        });

        return response()->success($customers, 'Customers fetched successfully');
    }

    public function store(CustomerRequest $request): JsonResponse
    {
        $data = array_merge(
            $request->validated(),
            $request->only($this->service->getCustomerCustomFieldColumnNames())
        );
        $customer = $this->service->createCustomer($data);

        return response()->success(
            new CustomerResource($customer->load(['customerGroup', 'discountPlans'])),
            'Customer created successfully',
            Response::HTTP_CREATED
        );
    }

    public function show(Customer $customer): JsonResponse
    {
        $customer = $this->service->getCustomer($customer);

        return response()->success(new CustomerResource($customer), 'Customer retrieved successfully');
    }

    public function update(CustomerRequest $request, Customer $customer): JsonResponse
    {
        $data = array_merge(
            $request->validated(),
            $request->only($this->service->getCustomerCustomFieldColumnNames())
        );
        $customer = $this->service->updateCustomer($customer, $data);

        return response()->success(new CustomerResource($customer), 'Customer updated successfully');
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->service->deleteCustomer($customer);

        return response()->success(null, 'Customer deleted successfully');
    }

    /**
     * Get all active customers (for dropdowns).
     */
    public function getAllActive(): JsonResponse
    {
        $customers = $this->service->getAllActive();

        return response()->success(CustomerResource::collection($customers), 'Active customers fetched successfully');
    }

    public function bulkDestroy(CustomerBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteCustomers($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} customers"
        );
    }

    public function import(ImportRequest $request): JsonResponse
    {
        $this->service->importCustomers($request->file('file'));

        return response()->success(null, 'Customers imported successfully');
    }

    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        $validated = $request->validated();
        $user = ($validated['method'] === 'email')
            ? User::findOrFail($validated['user_id'])
            : null;

        $filePath = $this->service->exportCustomers(
            $validated['ids'] ?? [],
            $validated['format'],
            $user,
            $validated['columns'] ?? [],
            $validated['method']
        );

        if ($validated['method'] === 'download') {
            return response()->download(Storage::disk('public')->path($filePath));
        }

        return response()->success(null, 'Export processed and sent via email');
    }

    /**
     * Get customer balance summary (opening_balance, total_sales, total_paid, total_returns, balance_due).
     */
    public function summary(Customer $customer): JsonResponse
    {
        $data = $this->service->getCustomerSummary($customer);

        return response()->success($data, 'Customer summary retrieved successfully');
    }

    /**
     * Get customer ledger (sales debits, payments/returns credits) with running balance.
     */
    public function ledger(Customer $customer): JsonResponse
    {
        $data = $this->service->getCustomerLedger($customer);

        return response()->success(['data' => $data], 'Customer ledger retrieved successfully');
    }

    /**
     * List deposits for a customer.
     */
    public function deposits(Customer $customer): JsonResponse
    {
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
        $validated = $request->validated();
        $deposit = $this->service->addDeposit(
            $customer,
            (float)$validated['amount'],
            $validated['note'] ?? null
        );

        return response()->success(
            new DepositResource($deposit->load('user')),
            'Deposit added successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Update a customer deposit (must belong to customer).
     */
    public function updateDeposit(CustomerDepositRequest $request, Customer $customer, Deposit $deposit): JsonResponse
    {
        if ($deposit->customer_id !== $customer->id) {
            abort(404, 'Deposit not found for this customer.');
        }
        $validated = $request->validated();
        $deposit = $this->service->updateDeposit(
            $deposit,
            (float)$validated['amount'],
            $validated['note'] ?? null
        );

        return response()->success(new DepositResource($deposit->load('user')), 'Deposit updated successfully');
    }

    /**
     * Delete a customer deposit.
     */
    public function destroyDeposit(Customer $customer, Deposit $deposit): JsonResponse
    {
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
        $validated = $request->validated();
        $point = $this->service->addPoint(
            $customer,
            (float)$validated['points'],
            $validated['note'] ?? null
        );

        return response()->success(
            new RewardPointResource($point),
            'Reward points added successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Update a customer reward point entry.
     */
    public function updatePoint(CustomerPointRequest $request, Customer $customer, RewardPoint $point): JsonResponse
    {
        if ($point->customer_id !== $customer->id) {
            abort(404, 'Reward point not found for this customer.');
        }
        $validated = $request->validated();
        $point = $this->service->updatePoint(
            $point,
            (float)$validated['points'],
            $validated['note'] ?? null
        );

        return response()->success(new RewardPointResource($point), 'Reward point updated successfully');
    }

    /**
     * Delete a customer reward point entry.
     */
    public function destroyPoint(Customer $customer, RewardPoint $point): JsonResponse
    {
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
        $payments = $this->service->getCustomerPayments($customer);
        $data = $payments->map(fn($p) => [
            'id' => $p->id,
            'created_at' => $p->created_at?->format('Y-m-d'),
            'payment_reference' => $p->payment_reference ?? '-',
            'amount' => number_format((float)$p->amount, 2),
            'paying_method' => ucfirst($p->paying_method ?? '-'),
            'payment_at' => $p->payment_at
                ? $p->payment_at->format('Y-m-d H:i')
                : $p->created_at?->format('Y-m-d H:i'),
        ]);

        return response()->success(['data' => $data], 'Customer payments retrieved successfully');
    }
}

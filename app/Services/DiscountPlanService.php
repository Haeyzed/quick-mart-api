<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DiscountPlan;
use App\Models\DiscountPlanCustomer;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * DiscountPlanService
 *
 * Handles all business logic for discount plan operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class DiscountPlanService extends BaseService
{
    /**
     * Get paginated list of discount plans with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, type, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<DiscountPlan>
     */
    public function getDiscountPlans(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return DiscountPlan::query()
            ->with('customers')
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['type'] ?? null),
                fn($query) => $query->where('type', $filters['type'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where('name', 'like', '%' . $filters['search'] . '%')
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single discount plan by ID.
     *
     * @param int $id Discount Plan ID
     * @return DiscountPlan
     */
    public function getDiscountPlan(int $id): DiscountPlan
    {
        return DiscountPlan::findOrFail($id);
    }

    /**
     * Create a new discount plan.
     *
     * @param array<string, mixed> $data Validated discount plan data
     * @return DiscountPlan
     */
    public function createDiscountPlan(array $data): DiscountPlan
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeDiscountPlanData($data);

            // Extract customer_ids if provided
            $customerIds = $data['customer_id'] ?? [];
            unset($data['customer_id']);

            $discountPlan = DiscountPlan::create($data);

            // Create customer assignments (matching old controller)
            foreach ($customerIds as $customerId) {
                DiscountPlanCustomer::create([
                    'discount_plan_id' => $discountPlan->id,
                    'customer_id' => $customerId,
                ]);
            }

            return $discountPlan->load('customers');
        });
    }

    /**
     * Normalize discount plan data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDiscountPlanData(array $data): array
    {
        // is_active is stored as boolean (true/false)
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        return $data;
    }

    /**
     * Update an existing discount plan.
     *
     * @param DiscountPlan $discountPlan Discount Plan instance to update
     * @param array<string, mixed> $data Validated discount plan data
     * @return DiscountPlan
     */
    public function updateDiscountPlan(DiscountPlan $discountPlan, array $data): DiscountPlan
    {
        return $this->transaction(function () use ($discountPlan, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeDiscountPlanData($data);

            // Extract customer_ids if provided
            $customerIds = $data['customer_id'] ?? [];
            unset($data['customer_id']);

            // Get previous customer IDs
            $previousCustomerIds = DiscountPlanCustomer::where('discount_plan_id', $discountPlan->id)
                ->pluck('customer_id')
                ->toArray();

            // Delete customer assignments that are no longer in the new list (matching old controller)
            foreach ($previousCustomerIds as $customerId) {
                if (!in_array($customerId, $customerIds)) {
                    DiscountPlanCustomer::where([
                        ['discount_plan_id', $discountPlan->id],
                        ['customer_id', $customerId],
                    ])->first()?->delete();
                }
            }

            // Create new customer assignments (matching old controller)
            foreach ($customerIds as $customerId) {
                if (!in_array($customerId, $previousCustomerIds)) {
                    DiscountPlanCustomer::create([
                        'discount_plan_id' => $discountPlan->id,
                        'customer_id' => $customerId,
                    ]);
                }
            }

            $discountPlan->update($data);
            return $discountPlan->fresh()->load('customers');
        });
    }

    /**
     * Bulk delete multiple discount plans.
     *
     * @param array<int> $ids Array of discount plan IDs to delete
     * @return int Number of discount plans deleted
     */
    public function bulkDeleteDiscountPlans(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $discountPlan = DiscountPlan::findOrFail($id);
                $this->deleteDiscountPlan($discountPlan);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete discount plan {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single discount plan.
     *
     * @param DiscountPlan $discountPlan Discount Plan instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteDiscountPlan(DiscountPlan $discountPlan): bool
    {
        return $this->transaction(function () use ($discountPlan) {
            if ($discountPlan->customers()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete discount plan: discount plan has associated customers');
            }

            if ($discountPlan->discounts()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete discount plan: discount plan has associated discounts');
            }

            return $discountPlan->delete();
        });
    }
}


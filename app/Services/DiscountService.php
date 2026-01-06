<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Discount;
use App\Models\DiscountPlanDiscount;
use App\Models\Product;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * DiscountService
 *
 * Handles all business logic for discount operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class DiscountService extends BaseService
{
    /**
     * Get paginated list of discounts with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: is_active, type, applicable_for, search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Discount>
     */
    public function getDiscounts(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Discount::query()
            ->with('discountPlans')
            ->when(
                isset($filters['is_active']),
                fn($query) => $query->where('is_active', (bool)$filters['is_active'])
            )
            ->when(
                !empty($filters['type'] ?? null),
                fn($query) => $query->where('type', $filters['type'])
            )
            ->when(
                !empty($filters['applicable_for'] ?? null),
                fn($query) => $query->where('applicable_for', $filters['applicable_for'])
            )
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where('name', 'like', '%' . $filters['search'] . '%')
            )
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get a single discount by ID.
     *
     * @param int $id Discount ID
     * @return Discount
     */
    public function getDiscount(int $id): Discount
    {
        return Discount::findOrFail($id);
    }

    /**
     * Create a new discount.
     *
     * @param array<string, mixed> $data Validated discount data
     * @return Discount
     */
    public function createDiscount(array $data): Discount
    {
        return $this->transaction(function () use ($data) {
            // Normalize data to match database schema
            $data = $this->normalizeDiscountData($data);

            // Extract discount_plan_ids if provided
            $discountPlanIds = $data['discount_plan_id'] ?? [];
            unset($data['discount_plan_id']);

            $discount = Discount::create($data);

            // Create discount plan assignments
            foreach ($discountPlanIds as $discountPlanId) {
                DiscountPlanDiscount::create([
                    'discount_id' => $discount->id,
                    'discount_plan_id' => $discountPlanId,
                ]);
            }

            return $discount->load('discountPlans');
        });
    }

    /**
     * Normalize discount data to match database schema requirements.
     *
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    private function normalizeDiscountData(array $data): array
    {
        // Set default is_active if not provided (matching old controller)
        if (!isset($data['is_active'])) {
            $data['is_active'] = false;
        } else {
            $data['is_active'] = (bool)filter_var(
                $data['is_active'],
                FILTER_VALIDATE_BOOLEAN,
                FILTER_NULL_ON_FAILURE
            );
        }

        // Ensure days is an array if it's a string (for consistency)
        if (isset($data['days']) && is_string($data['days'])) {
            $data['days'] = explode(',', $data['days']);
        }

        // Ensure product_list is an array if it's a string (for consistency)
        if (isset($data['product_list']) && is_string($data['product_list'])) {
            $data['product_list'] = explode(',', $data['product_list']);
        }

        return $data;
    }

    /**
     * Update an existing discount.
     *
     * @param Discount $discount Discount instance to update
     * @param array<string, mixed> $data Validated discount data
     * @return Discount
     */
    public function updateDiscount(Discount $discount, array $data): Discount
    {
        return $this->transaction(function () use ($discount, $data) {
            // Normalize data to match database schema
            $data = $this->normalizeDiscountData($data);

            // Extract discount_plan_ids if provided
            $discountPlanIds = $data['discount_plan_id'] ?? [];
            unset($data['discount_plan_id']);

            // Get previous discount plan IDs
            $previousDiscountPlanIds = DiscountPlanDiscount::where('discount_id', $discount->id)
                ->pluck('discount_plan_id')
                ->toArray();

            // Delete discount plan assignments that are no longer in the new list
            foreach ($previousDiscountPlanIds as $discountPlanId) {
                if (!in_array($discountPlanId, $discountPlanIds)) {
                    DiscountPlanDiscount::where([
                        ['discount_plan_id', $discountPlanId],
                        ['discount_id', $discount->id],
                    ])->first()?->delete();
                }
            }

            // Create new discount plan assignments
            foreach ($discountPlanIds as $discountPlanId) {
                if (!in_array($discountPlanId, $previousDiscountPlanIds)) {
                    DiscountPlanDiscount::create([
                        'discount_plan_id' => $discountPlanId,
                        'discount_id' => $discount->id,
                    ]);
                }
            }

            $discount->update($data);
            return $discount->fresh()->load('discountPlans');
        });
    }

    /**
     * Bulk delete multiple discounts.
     *
     * @param array<int> $ids Array of discount IDs to delete
     * @return int Number of discounts deleted
     */
    public function bulkDeleteDiscounts(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $discount = Discount::findOrFail($id);
                $this->deleteDiscount($discount);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete discount {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single discount.
     *
     * @param Discount $discount Discount instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteDiscount(Discount $discount): bool
    {
        return $this->transaction(function () use ($discount) {
            if ($discount->discountPlans()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete discount: discount has associated discount plans');
            }

            return $discount->delete();
        });
    }

    /**
     * Search for a product by code.
     *
     * @param string $code Product code
     * @return array{0: int, 1: string, 2: string}|null Returns [id, name, code] or null if not found
     */
    public function productSearch(string $code): ?array
    {
        $product = Product::where([
            ['code', $code],
            ['is_active', true],
        ])->select('id', 'name', 'code')->first();

        if (!$product) {
            return null;
        }

        return [
            $product->id,
            $product->name,
            $product->code,
        ];
    }
}


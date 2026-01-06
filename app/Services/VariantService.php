<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Variant;
use Exception;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;

/**
 * VariantService
 *
 * Handles all business logic for variant operations including CRUD operations,
 * filtering, pagination, and bulk operations.
 */
class VariantService extends BaseService
{
    /**
     * Get paginated list of variants with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: search
     * @param int $perPage Number of items per page (default: 10)
     * @return LengthAwarePaginator<Variant>
     */
    public function getVariants(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return Variant::query()
            ->when(
                !empty($filters['search'] ?? null),
                fn($query) => $query->where('name', 'like', '%' . $filters['search'] . '%')
            )
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get a single variant by ID.
     *
     * @param int $id Variant ID
     * @return Variant
     */
    public function getVariant(int $id): Variant
    {
        return Variant::findOrFail($id);
    }

    /**
     * Create a new variant.
     *
     * @param array<string, mixed> $data Validated variant data
     * @return Variant
     */
    public function createVariant(array $data): Variant
    {
        return $this->transaction(function () use ($data) {
            return Variant::create($data);
        });
    }

    /**
     * Update an existing variant.
     *
     * @param Variant $variant Variant instance to update
     * @param array<string, mixed> $data Validated variant data
     * @return Variant
     */
    public function updateVariant(Variant $variant, array $data): Variant
    {
        return $this->transaction(function () use ($variant, $data) {
            $variant->update($data);
            return $variant->fresh();
        });
    }

    /**
     * Bulk delete multiple variants.
     *
     * @param array<int> $ids Array of variant IDs to delete
     * @return int Number of variants deleted
     */
    public function bulkDeleteVariants(array $ids): int
    {
        $deletedCount = 0;

        foreach ($ids as $id) {
            try {
                $variant = Variant::findOrFail($id);
                $this->deleteVariant($variant);
                $deletedCount++;
            } catch (Exception $e) {
                // Log error but continue with other deletions
                $this->logError("Failed to delete variant {$id}: " . $e->getMessage());
            }
        }

        return $deletedCount;
    }

    /**
     * Delete a single variant.
     *
     * @param Variant $variant Variant instance to delete
     * @return bool
     * @throws HttpResponseException
     */
    public function deleteVariant(Variant $variant): bool
    {
        return $this->transaction(function () use ($variant) {
            if ($variant->products()->exists()) {
                abort(Response::HTTP_UNPROCESSABLE_ENTITY, 'Cannot delete variant: variant has associated products');
            }

            return $variant->delete();
        });
    }
}


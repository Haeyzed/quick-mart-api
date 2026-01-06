<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * ExpenseCategoryResource
 *
 * API resource for transforming ExpenseCategory model data.
 */
class ExpenseCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            /**
             * Expense Category ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Unique code identifier for the expense category.
             *
             * @var string $code
             * @example EXP001
             */
            'code' => $this->code,

            /**
             * Expense category name.
             *
             * @var string $name
             * @example Office Supplies
             */
            'name' => $this->name,

            /**
             * Whether the expense category is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the expense category was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the expense category was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}


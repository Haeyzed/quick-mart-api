<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * IncomeCategoryResource
 *
 * API resource for transforming IncomeCategory model data.
 */
class IncomeCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            /**
             * Income Category ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Unique code identifier for the income category.
             *
             * @var string $code
             * @example INC001
             */
            'code' => $this->code,

            /**
             * Income category name.
             *
             * @var string $name
             * @example Sales Revenue
             */
            'name' => $this->name,

            /**
             * Whether the income category is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * Timestamp when the income category was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the income category was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T00:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}


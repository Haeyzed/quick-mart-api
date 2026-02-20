<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class CategoryOptionResource
 *
 * Transforms a Category into a minimal { value, label } shape for combobox/select options.
 */
class CategoryOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array{value: int, label: string}
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * The category ID (value for select).
             *
             * @example 1
             */
            'value' => $this->id,

            /**
             * The category name (label for select).
             *
             * @example Electronics
             */
            'label' => $this->name,
        ];
    }
}

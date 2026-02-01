<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * CategoryOptionResource
 *
 * Transforms a Category into a minimal { value, label } shape for combobox/select options.
 */
class CategoryOptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array{value: int, label: string}
     */
    public function toArray($request): array
    {
        return [
            'value' => $this->id,
            'label' => $this->name,
        ];
    }
}

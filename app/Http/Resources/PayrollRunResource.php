<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollRunResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'month' => $this->month,
            'year' => $this->year,
            'status' => $this->status,
            'generated_by' => $this->generated_by,
            'generated_by_user' => $this->whenLoaded('generatedByUser', fn () => [
                'id' => $this->generatedByUser->id,
                'name' => $this->generatedByUser->name,
            ]),
            'entries_count' => $this->when(isset($this->entries_count), fn () => $this->entries_count),
            'entries' => PayrollEntryResource::collection($this->whenLoaded('entries')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Candidate;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Candidate
 */
class CandidateResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /** @example 1 */
            'id' => $this->id,
            /** @example 5 */
            'job_opening_id' => $this->job_opening_id,
            /** @example "Jane Doe" */
            'name' => $this->name,
            /** @example "jane@example.com" */
            'email' => $this->email,
            /** @example "+1234567890" */
            'phone' => $this->phone,
            /** @example "LinkedIn" */
            'source' => $this->source,
            /** @example "screening" */
            'stage' => $this->stage,
            /** @example "2024-01-15T10:00:00Z" */
            'stage_updated_at' => $this->stage_updated_at?->toIso8601String(),
            /** @example "Strong profile" */
            'notes' => $this->notes,
            /** @example {"id": 5, "title": "Senior Developer"} */
            'job_opening' => $this->whenLoaded('jobOpening', fn() => ['id' => $this->jobOpening->id, 'title' => $this->jobOpening->title]),
            /** @example "2024-01-10T08:00:00Z" */
            'created_at' => $this->created_at?->toIso8601String(),
            /** @example "2024-01-15T10:00:00Z" */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

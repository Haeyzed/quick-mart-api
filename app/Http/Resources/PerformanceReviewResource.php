<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerformanceReviewResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employee_id,
            'review_period_start' => $this->review_period_start?->toDateString(),
            'review_period_end' => $this->review_period_end?->toDateString(),
            'reviewer_id' => $this->reviewer_id,
            'overall_rating' => $this->overall_rating ? (float) $this->overall_rating : null,
            'status' => $this->status,
            'notes' => $this->notes,
            'promotion_effective_date' => $this->promotion_effective_date?->toDateString(),
            'new_designation_id' => $this->new_designation_id,
            'submitted_at' => $this->submitted_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

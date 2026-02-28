<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
            'id' => $this->id,
            'job_opening_id' => $this->job_opening_id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'source' => $this->source,
            'stage' => $this->stage,
            'stage_updated_at' => $this->stage_updated_at?->toIso8601String(),
            'notes' => $this->notes,
            'job_opening' => $this->whenLoaded('jobOpening', fn () => ['id' => $this->jobOpening->id, 'title' => $this->jobOpening->title]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

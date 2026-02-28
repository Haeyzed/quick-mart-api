<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InterviewResource extends JsonResource
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
            'candidate_id' => $this->candidate_id,
            'scheduled_at' => $this->scheduled_at?->toIso8601String(),
            'duration_minutes' => $this->duration_minutes,
            'interviewer_id' => $this->interviewer_id,
            'feedback' => $this->feedback,
            'status' => $this->status,
            'candidate' => $this->whenLoaded('candidate', fn () => ['id' => $this->candidate->id, 'name' => $this->candidate->name, 'email' => $this->candidate->email]),
            'interviewer' => $this->whenLoaded('interviewer', fn () => ['id' => $this->interviewer->id, 'name' => $this->interviewer->name]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

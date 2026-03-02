<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\JobOpening;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin JobOpening
 */
class JobOpeningResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * The unique identifier for the job opening.
             *
             * @example 1
             */
            'id' => $this->id,
            /**
             * The title of the job opening.
             *
             * @example "Senior PHP Developer"
             */
            'title' => $this->title,
            /**
             * The ID of the department.
             *
             * @example 2
             */
            'department_id' => $this->department_id,
            /**
             * The department (if relation is loaded).
             *
             * @example {"id": 2, "name": "Engineering"}
             */
            'department' => $this->whenLoaded('department', fn () => [
                'id' => $this->department->id,
                'name' => $this->department->name,
            ]),
            /**
             * The ID of the designation.
             *
             * @example 3
             */
            'designation_id' => $this->designation_id,
            /**
             * The designation (if relation is loaded).
             *
             * @example {"id": 3, "name": "Senior Developer"}
             */
            'designation' => $this->whenLoaded('designation', fn () => [
                'id' => $this->designation->id,
                'name' => $this->designation->name,
            ]),
            /**
             * The status of the job opening.
             *
             * @example "open"
             */
            'status' => $this->status,
            /**
             * The job description.
             *
             * @example "We are looking for..."
             */
            'description' => $this->description,
            /**
             * The number of openings.
             *
             * @example 2
             */
            'openings_count' => $this->openings_count,
            /**
             * The ID of the user who created the job opening.
             *
             * @example 1
             */
            'created_by' => $this->created_by,
            /**
             * The designation (if relation is loaded).
             *
             * @example {"id": 1, "name": "John Doe"}
             */
            'created_by' => $this->whenLoaded('createdBy', fn () => [
                'id' => $this->createdBy->id,
                'name' => $this->createdBy->name,
                'email' => $this->createdBy->email,
                'phone_number' => $this->createdBy->phone_number,
                'image_url' => $this->createdBy->image_url,
            ]),
            /**
             * The date and time when the job opening was created.
             *
             * @example 2024-11-20T08:30:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),
            /**
             * The date and time when the job opening was last updated.
             *
             * @example 2024-11-21T09:15:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

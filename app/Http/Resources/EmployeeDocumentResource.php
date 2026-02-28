<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeDocumentResource extends JsonResource
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
            'document_type_id' => $this->document_type_id,
            'name' => $this->name,
            'file_path' => $this->file_path,
            'file_url' => $this->file_url,
            'issue_date' => $this->issue_date?->toDateString(),
            'expiry_date' => $this->expiry_date?->toDateString(),
            'is_expired' => $this->resource->isExpired(),
            'notes' => $this->notes,
            'document_type' => $this->whenLoaded('documentType', fn () => ['id' => $this->documentType->id, 'name' => $this->documentType->name, 'code' => $this->documentType->code]),
            'employee' => $this->whenLoaded('employee', fn () => ['id' => $this->employee->id, 'name' => $this->employee->name, 'employee_code' => $this->employee->employee_code]),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

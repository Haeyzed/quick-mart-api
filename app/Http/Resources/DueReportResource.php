<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * DueReportResource
 *
 * API resource for a single customer due report row (sale with due amount).
 */
class DueReportResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $row = is_array($this->resource) ? $this->resource : (array)$this->resource;

        return [
            'id' => $row['id'] ?? null,
            'date' => $row['date'] ?? null,
            'reference_no' => $row['reference_no'] ?? null,
            'customer_name' => $row['customer_name'] ?? '—',
            'customer_phone' => $row['customer_phone'] ?? '—',
            'grand_total' => (float)($row['grand_total'] ?? 0),
            'returned_amount' => (float)($row['returned_amount'] ?? 0),
            'paid' => (float)($row['paid'] ?? 0),
            'due' => (float)($row['due'] ?? 0),
        ];
    }
}

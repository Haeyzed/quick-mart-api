<?php

declare(strict_types=1);

namespace App\Services;

use App\Traits\CheckPermissionsTrait;
use Illuminate\Support\Collection;
use OwenIt\Auditing\Models\Audit;

/**
 * Utility service for miscellaneous API data.
 *
 * Provides auditable models list (distinct from audits) for combobox/filters.
 */
class UtilityService extends BaseService
{
    use CheckPermissionsTrait;

    /**
     * Get distinct auditable_type values from audits table.
     *
     * Requires audit-logs-index permission.
     *
     * @return Collection<int, array{value: string, label: string}>
     */
    public function getAuditableModels(): Collection
    {
        $this->requirePermission('audit-logs-index');

        $types = Audit::query()
            ->select('auditable_type')
            ->distinct()
            ->orderBy('auditable_type')
            ->pluck('auditable_type');

        return $types->map(function (string $type): array {
            $parts = explode('\\', $type);

            return [
                'value' => $type,
                'label' => end($parts) ?: $type,
            ];
        })->values();
    }
}

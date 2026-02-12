<?php

declare(strict_types=1);

namespace App\Http\Requests\Reports\Concerns;

use Illuminate\Validation\Rule;

trait HasReportExportRules
{
    /**
     * Common export rules: format, method, columns, user_id.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function exportRules(): array
    {
        return [
            'format' => ['required', 'string', Rule::in(['excel', 'pdf'])],
            'method' => ['required', 'string', Rule::in(['download', 'email'])],
            'columns' => ['required', 'array', 'min:1'],
            'columns.*' => ['string'],
            'user_id' => ['required_if:method,email', 'integer', 'exists:users,id'],
        ];
    }
}

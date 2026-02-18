<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Class LanguageService
 * Handles business logic for Languages (World reference data).
 */
class LanguageService
{
    /**
     * Get paginated languages.
     *
     * @param  array<string, mixed>  $filters
     */
    public function getPaginatedLanguages(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Language::query()
            ->filter($filters)
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Get list of language options (value/label format).
     */
    public function getOptions(): Collection
    {
        return Language::query()
            ->select('id', 'code', 'name', 'name_native', 'dir')
            ->orderBy('name')
            ->get()
            ->map(fn (Language $language) => [
                'value' => $language->id,
                'label' => $language->name,
                'code' => $language->code,
                'name_native' => $language->name_native,
                'dir' => $language->dir,
            ]);
    }
}

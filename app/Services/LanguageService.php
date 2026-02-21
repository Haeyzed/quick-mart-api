<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\LanguagesExport;
use App\Imports\LanguagesImport;
use App\Models\Language;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Excel;
use Maatwebsite\Excel\Facades\Excel as ExcelFacade;
use RuntimeException;

/**
 * Class LanguageService
 * Handles business logic for Languages (World reference data).
 */
class LanguageService
{
    private const TEMPLATE_PATH = 'Imports/Templates';

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

    public function createLanguage(array $data): Language
    {
        return DB::transaction(function () use ($data) {
            return Language::query()->create($data);
        });
    }

    public function updateLanguage(Language $language, array $data): Language
    {
        return DB::transaction(function () use ($language, $data) {
            $language->update($data);

            return $language->fresh();
        });
    }

    public function deleteLanguage(Language $language): void
    {
        DB::transaction(function () use ($language) {
            $language->delete();
        });
    }

    public function bulkDeleteLanguages(array $ids): int
    {
        return DB::transaction(function () use ($ids) {
            return Language::query()->whereIn('id', $ids)->delete();
        });
    }

    public function importLanguages(UploadedFile $file): void
    {
        ExcelFacade::import(new LanguagesImport, $file);
    }

    public function download(): string
    {
        $fileName = 'languages-sample.csv';
        $path = app_path(self::TEMPLATE_PATH.'/'.$fileName);

        if (! File::exists($path)) {
            throw new RuntimeException('Template languages not found.');
        }

        return $path;
    }

    public function generateExportFile(array $ids, string $format, array $columns, array $filters = []): string
    {
        $fileName = 'languages_'.now()->timestamp;
        $relativePath = 'exports/'.$fileName.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $writerType = $format === 'pdf' ? Excel::DOMPDF : Excel::XLSX;

        ExcelFacade::store(
            new LanguagesExport($ids, $columns, $filters),
            $relativePath,
            'public',
            $writerType
        );

        return $relativePath;
    }
}

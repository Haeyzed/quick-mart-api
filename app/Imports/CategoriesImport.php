<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Category;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithUpserts;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Excel/CSV import for Category entities with batching and upsert support.
 */
class CategoriesImport implements
    ToModel,
    WithHeadingRow,
    WithValidation,
    WithUpserts,
    WithBatchInserts,
    WithChunkReading,
    SkipsEmptyRows
{
    /**
     * @param array<string, mixed> $row
     * @return Category|null
     */
    public function model(array $row): ?Category
    {
        $name = trim((string)($row['name'] ?? ''));

        if ($name === '') {
            return null;
        }

        $parentId = null;
        if (!empty($row['parentcategory'])) {
            $parentName = trim((string)$row['parentcategory']);
            $parent = Category::query()->firstOrCreate(
                ['name' => $parentName],
                ['is_active' => true]
            );
            $parentId = $parent->id;
        }

        return new Category([
            'name' => $name,
            'slug' => Str::slug($name),
            'parent_id' => $parentId,
            'page_title' => $row['page_title'] ?? null,
            'short_description' => $row['short_description'] ?? null,
            'is_active' => isset($row['is_active']) ? filter_var($row['is_active'], FILTER_VALIDATE_BOOLEAN) : true,
            'featured' => filter_var($row['featured'] ?? false, FILTER_VALIDATE_BOOLEAN),
            'is_sync_disable' => filter_var($row['is_sync_disable'] ?? false, FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    /**
     * @return string
     */
    public function uniqueBy(): string
    {
        return 'name';
    }

    /**
     * @return array[]
     */
    public function rules(): array
    {
        return [
            'name'              => ['required', 'string', 'max:255'],
            'page_title'        => ['nullable', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'parentcategory'    => ['nullable', 'string', 'max:255'],
            'is_active'         => ['nullable', 'boolean'],
            'featured'          => ['nullable', 'boolean'],
            'is_sync_disable'   => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 1000;
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}

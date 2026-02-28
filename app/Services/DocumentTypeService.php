<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DocumentType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DocumentTypeService
{
    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<DocumentType>
     */
    public function getPaginated(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        $query = DocumentType::query()->latest();

        if (isset($filters['is_active'])) {
            $query->when(
                $filters['is_active'],
                fn ($q) => $q->active(),
                fn ($q) => $q->where('is_active', false)
            );
        }
        if (! empty($filters['search'])) {
            $term = '%'.$filters['search'].'%';
            $query->where('name', 'like', $term)->orWhere('code', 'like', $term);
        }

        return $query->paginate($perPage);
    }

    /**
     * @return Collection<int, array{value: int, label: string}>
     */
    public function getOptions(): Collection
    {
        return DocumentType::active()
            ->select('id', 'name', 'code')
            ->orderBy('name')
            ->get()
            ->map(fn (DocumentType $type) => [
                'value' => $type->id,
                'label' => $type->name,
            ]);
    }

    public function create(array $data): DocumentType
    {
        return DB::transaction(fn () => DocumentType::query()->create($data));
    }

    public function update(DocumentType $documentType, array $data): DocumentType
    {
        return DB::transaction(function () use ($documentType, $data) {
            $documentType->update($data);

            return $documentType->fresh();
        });
    }

    public function delete(DocumentType $documentType): void
    {
        DB::transaction(fn () => $documentType->delete());
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkDelete(array $ids): int
    {
        return DB::transaction(fn () => DocumentType::query()->whereIn('id', $ids)->delete());
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkUpdateStatus(array $ids, bool $isActive): int
    {
        return DocumentType::query()->whereIn('id', $ids)->update(['is_active' => $isActive]);
    }
}

<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\SuppliersExport;
use App\Imports\SuppliersImport;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Supplier;
use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class SupplierService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    private const DEFAULT_SUPPLIER_IMAGES_PATH = 'images/supplier';

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    public function getSupplier(Supplier $supplier): Supplier
    {
        $this->requirePermission('suppliers-index');

        return $supplier->fresh();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Supplier>
     */
    public function getSuppliers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('suppliers-index');

        return Supplier::query()
            ->when(isset($filters['status']), fn ($q) => $q->where('is_active', $filters['status'] === 'active'))
            ->when(! empty($filters['search']), function ($q) use ($filters) {
                $term = '%'.$filters['search'].'%';
                $q->where(fn ($subQ) => $subQ
                    ->where('name', 'like', $term)
                    ->orWhere('company_name', 'like', $term)
                    ->orWhere('email', 'like', $term)
                    ->orWhere('phone_number', 'like', $term)
                );
            })
            ->latest()
            ->paginate($perPage);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function createSupplier(array $data): Supplier
    {
        $this->requirePermission('suppliers-create');

        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data = $this->handleImageUpload($data);
            }
            $data['is_active'] = $data['is_active'] ?? true;

            return Supplier::create($data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateSupplier(Supplier $supplier, array $data): Supplier
    {
        $this->requirePermission('suppliers-update');

        return DB::transaction(function () use ($supplier, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($supplier->image) {
                    $this->uploadService->delete($supplier->image);
                }
                $data = $this->handleImageUpload($data);
            }

            $supplier->update($data);

            return $supplier->fresh();
        });
    }

    public function deleteSupplier(Supplier $supplier): void
    {
        $this->requirePermission('suppliers-delete');

        DB::transaction(function () use ($supplier) {
            if ($supplier->image) {
                $this->uploadService->delete($supplier->image);
            }
            $supplier->update(['is_active' => false]);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Supplier>
     */
    public function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        $this->requirePermission('suppliers-index');

        return Supplier::query()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkDeleteSuppliers(array $ids): int
    {
        $this->requirePermission('suppliers-delete');

        $count = 0;
        foreach ($ids as $id) {
            $supplier = Supplier::find($id);
            if ($supplier) {
                $this->deleteSupplier($supplier);
                $count++;
            }
        }

        return $count;
    }

    public function bulkActivateSuppliers(array $ids): int
    {
        $this->requirePermission('suppliers-update');

        return Supplier::whereIn('id', $ids)->update(['is_active' => true]);
    }

    public function bulkDeactivateSuppliers(array $ids): int
    {
        $this->requirePermission('suppliers-update');

        return Supplier::whereIn('id', $ids)->update(['is_active' => false]);
    }

    public function importSuppliers(UploadedFile $file): void
    {
        $this->requirePermission('suppliers-import');
        Excel::import(new SuppliersImport, $file);
    }

    /**
     * @param  array<int>  $ids
     * @param  array<string>  $columns
     */
    public function exportSuppliers(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('suppliers-export');

        $fileName = 'suppliers_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/'.$fileName;

        if ($format === 'excel') {
            Excel::store(new SuppliersExport($ids, $columns), $relativePath, 'public');
        } else {
            $suppliers = Supplier::query()
                ->when(! empty($ids), fn ($q) => $q->whereIn('id', $ids))
                ->orderBy('company_name')
                ->get();

            $pdf = PDF::loadView('exports.suppliers-pdf', compact('suppliers', 'columns'));
            Storage::disk('public')->put($relativePath, $pdf->output());
        }

        if ($method === 'email' && $user) {
            $this->sendExportEmail($user, $relativePath, $fileName);
        }

        return $relativePath;
    }

    private function sendExportEmail(User $user, string $path, string $fileName): void
    {
        $mailSetting = MailSetting::default()->firstOr(
            fn () => throw new RuntimeException('Mail settings are not configured.')
        );
        $generalSetting = GeneralSetting::latest()->first();
        $this->setMailInfo($mailSetting);
        Mail::to($user->email)->send(
            new ExportMail($user, $path, $fileName, 'Suppliers List', $generalSetting)
        );
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function handleImageUpload(array $data): array
    {
        $path = $this->uploadService->upload(
            $data['image'],
            config('storage.suppliers.images', self::DEFAULT_SUPPLIER_IMAGES_PATH)
        );

        $data['image'] = $path;

        return $data;
    }
}

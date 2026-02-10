<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\BillersExport;
use App\Imports\BillersImport;
use App\Mail\ExportMail;
use App\Models\Biller;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
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

/**
 * Service class for Biller CRUD and listing.
 *
 * Follows the same pattern as BrandService: index with filters, create/update with
 * optional image upload, soft delete via is_active.
 */
class BillerService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    private const DEFAULT_BILLER_IMAGES_PATH = 'images/biller';

    public function __construct(
        private readonly UploadService $uploadService
    ) {}

    public function getBiller(Biller $biller): Biller
    {
        $this->requirePermission('billers-index');

        return $biller->fresh();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return LengthAwarePaginator<Biller>
     */
    public function getBillers(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('billers-index');

        return Biller::query()
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
    public function createBiller(array $data): Biller
    {
        $this->requirePermission('billers-create');

        return DB::transaction(function () use ($data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                $data = $this->handleImageUpload($data);
            }
            $data['is_active'] = $data['is_active'] ?? true;

            return Biller::create($data);
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateBiller(Biller $biller, array $data): Biller
    {
        $this->requirePermission('billers-update');

        return DB::transaction(function () use ($biller, $data) {
            if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
                if ($biller->image) {
                    $this->uploadService->delete($biller->image);
                }
                $data = $this->handleImageUpload($data);
            }

            $biller->update($data);

            return $biller->fresh();
        });
    }

    public function deleteBiller(Biller $biller): void
    {
        $this->requirePermission('billers-delete');

        DB::transaction(function () use ($biller) {
            if ($biller->image) {
                $this->uploadService->delete($biller->image);
            }
            $biller->update(['is_active' => false]);
        });
    }

    /**
     * @return \Illuminate\Database\Eloquent\Collection<int, Biller>
     */
    public function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        $this->requirePermission('billers-index');

        return Biller::query()->where('is_active', true)->orderBy('name')->get();
    }

    /**
     * @param  array<int>  $ids
     */
    public function bulkDeleteBillers(array $ids): int
    {
        $this->requirePermission('billers-delete');

        $count = 0;
        foreach ($ids as $id) {
            $biller = Biller::find($id);
            if ($biller) {
                $this->deleteBiller($biller);
                $count++;
            }
        }

        return $count;
    }

    public function bulkActivateBillers(array $ids): int
    {
        $this->requirePermission('billers-update');

        return Biller::whereIn('id', $ids)->update(['is_active' => true]);
    }

    public function bulkDeactivateBillers(array $ids): int
    {
        $this->requirePermission('billers-update');

        return Biller::whereIn('id', $ids)->update(['is_active' => false]);
    }

    public function importBillers(UploadedFile $file): void
    {
        $this->requirePermission('billers-import');
        Excel::import(new BillersImport, $file);
    }

    /**
     * @param  array<int>  $ids
     * @param  array<string>  $columns
     */
    public function exportBillers(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('billers-export');

        $fileName = 'billers_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/'.$fileName;

        if ($format === 'excel') {
            Excel::store(new BillersExport($ids, $columns), $relativePath, 'public');
        } else {
            $billers = Biller::query()
                ->when(! empty($ids), fn ($q) => $q->whereIn('id', $ids))
                ->orderBy('company_name')
                ->get();

            $pdf = PDF::loadView('exports.billers-pdf', compact('billers', 'columns'));
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
            new ExportMail($user, $path, $fileName, 'Billers List', $generalSetting)
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
            config('storage.billers.images', self::DEFAULT_BILLER_IMAGES_PATH)
        );

        $data['image'] = $path;

        return $data;
    }
}

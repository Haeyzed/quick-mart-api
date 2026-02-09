<?php

declare(strict_types=1);

namespace App\Services;

use App\Exports\AuditsExport;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Traits\CheckPermissionsTrait;
use App\Traits\MailInfo;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use OwenIt\Auditing\Models\Audit;
use RuntimeException;

/**
 * Service class for Audit Log operations.
 *
 * Uses Laravel Auditing (owen-it/laravel-auditing) as the source.
 * Returns raw Audit records without transformation.
 * Users with role_id > 2 only see their own audits.
 */
class AuditLogService extends BaseService
{
    use CheckPermissionsTrait, MailInfo;

    /**
     * Retrieve audits with optional filters and pagination.
     *
     * Role-based: users with role_id > 2 only see their own audits.
     * Requires audit-logs-index permission.
     *
     * @param  array<string, mixed>  $filters  Associative array with optional keys: search, event, auditable_type, ip_address, user.
     * @param  int  $perPage  Number of items per page.
     * @return LengthAwarePaginator<Audit>
     */
    public function getAudits(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $this->requirePermission('audit-logs-index');

        $user = Auth::user();

        $query = Audit::query()
            ->with('user')
            ->when($user && $user->role_id > 2, fn ($q) => $q->where('audits.user_id', $user->id))
            ->when(! empty($filters['search'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['search']}%";
                $q->where(fn ($subQ) => $subQ
                    ->where('audits.event', 'like', $term)
                    ->orWhere('audits.auditable_type', 'like', $term)
                    ->orWhere('audits.auditable_id', 'like', $term)
                    ->orWhere('audits.tags', 'like', $term)
                    ->orWhereHas('user', fn ($u) => $u->where('name', 'like', $term))
                );
            })
            ->when(! empty($filters['event'] ?? null), fn ($q) => $q->where('audits.event', $filters['event']))
            ->when(! empty($filters['auditable_type'] ?? null), fn ($q) => $q->where('audits.auditable_type', 'like', "%{$filters['auditable_type']}%"))
            ->when(! empty($filters['ip_address'] ?? null), fn ($q) => $q->where('audits.ip_address', 'like', "%{$filters['ip_address']}%"))
            ->when(! empty($filters['user'] ?? null), function ($q) use ($filters) {
                $term = "%{$filters['user']}%";
                $q->whereHas('user', fn ($u) => $u->where('name', 'like', $term));
            })
            ->orderByDesc('audits.id');

        return $query->paginate($perPage);
    }

    /**
     * Export audits to Excel or PDF.
     *
     * @param  array<int>  $ids  Audit IDs. Empty exports all.
     * @param  string  $format  'excel' or 'pdf'.
     * @param  User|null  $user  Recipient when method is 'email'.
     * @param  array<string>  $columns  Column keys to include.
     * @param  string  $method  'download' or 'email'.
     * @return string Relative storage path of the generated file.
     */
    public function exportAudits(array $ids, string $format, ?User $user, array $columns, string $method): string
    {
        $this->requirePermission('audit-logs-export');

        $fileName = 'audits_'.now()->timestamp.'.'.($format === 'pdf' ? 'pdf' : 'xlsx');
        $relativePath = 'exports/'.$fileName;

        if ($format === 'excel') {
            Excel::store(new AuditsExport($ids, $columns), $relativePath, 'public');
        } else {
            $audits = Audit::query()
                ->with('user')
                ->when(! empty($ids), fn ($q) => $q->whereIn('id', $ids))
                ->orderByDesc('id')
                ->get();
            $pdf = PDF::loadView('exports.audits-pdf', compact('audits', 'columns'));
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
            new ExportMail($user, $path, $fileName, 'Audit Log', $generalSetting)
        );
    }
}

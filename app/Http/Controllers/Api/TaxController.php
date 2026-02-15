<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Taxes\StoreTaxRequest;
use App\Http\Requests\Taxes\TaxBulkActionRequest;
use App\Http\Requests\Taxes\UpdateTaxRequest;
use App\Http\Resources\TaxResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\Tax;
use App\Models\User;
use App\Services\TaxService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class TaxController
 *
 * API Controller for Tax CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to TaxService.
 *
 * @group Tax Management
 */
class TaxController extends Controller
{
    /**
     * TaxController constructor.
     */
    public function __construct(
        private readonly TaxService $service
    )
    {
    }

    /**
     * Display a paginated listing of taxes.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view taxes')) {
            return response()->forbidden('Permission denied for viewing taxes list.');
        }

        $taxes = $this->service->getPaginatedTaxes(
            $request->all(),
            (int)$request->input('per_page', 10)
        );

        return response()->success(
            TaxResource::collection($taxes),
            'Taxes retrieved successfully'
        );
    }

    /**
     * Get tax options for select components.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view taxes')) {
            return response()->forbidden('Permission denied for viewing taxes options.');
        }

        return response()->success($this->service->getOptions(), 'Tax options retrieved successfully');
    }

    /**
     * Store a newly created tax.
     */
    public function store(StoreTaxRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create taxes')) {
            return response()->forbidden('Permission denied for create tax.');
        }

        $tax = $this->service->createTax($request->validated());

        return response()->success(
            new TaxResource($tax),
            'Tax created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Display the specified tax.
     */
    public function show(Tax $tax): JsonResponse
    {
        if (auth()->user()->denies('view tax details')) {
            return response()->forbidden('Permission denied for view tax.');
        }

        return response()->success(
            new TaxResource($tax),
            'Tax details retrieved successfully'
        );
    }

    /**
     * Update the specified tax.
     */
    public function update(UpdateTaxRequest $request, Tax $tax): JsonResponse
    {
        if (auth()->user()->denies('update taxes')) {
            return response()->forbidden('Permission denied for update tax.');
        }

        $updatedTax = $this->service->updateTax($tax, $request->validated());

        return response()->success(
            new TaxResource($updatedTax),
            'Tax updated successfully'
        );
    }

    /**
     * Remove the specified tax (soft delete).
     */
    public function destroy(Tax $tax): JsonResponse
    {
        if (auth()->user()->denies('delete taxes')) {
            return response()->forbidden('Permission denied for delete tax.');
        }

        $this->service->deleteTax($tax);

        return response()->success(null, 'Tax deleted successfully');
    }

    /**
     * Bulk delete taxes.
     */
    public function bulkDestroy(TaxBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete taxes')) {
            return response()->forbidden('Permission denied for bulk delete taxes.');
        }

        $count = $this->service->bulkDeleteTaxes($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} taxes"
        );
    }

    /**
     * Bulk activate taxes.
     */
    public function bulkActivate(TaxBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update taxes')) {
            return response()->forbidden('Permission denied for bulk update taxes.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} taxes activated"
        );
    }

    /**
     * Bulk deactivate taxes.
     */
    public function bulkDeactivate(TaxBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update taxes')) {
            return response()->forbidden('Permission denied for bulk update taxes.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} taxes deactivated"
        );
    }

    /**
     * Import taxes from Excel/CSV.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import taxes')) {
            return response()->forbidden('Permission denied for import taxes.');
        }

        $this->service->importTaxes($request->file('file'));

        return response()->success(null, 'Taxes imported successfully');
    }

    /**
     * Export taxes to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export taxes')) {
            return response()->forbidden('Permission denied for export taxes.');
        }

        $validated = $request->validated();

        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

        if ($validated['method'] === 'email') {
            $userId = $validated['user_id'] ?? auth()->id();
            $user = User::query()->find($userId);

            if (!$user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();
            if (!$mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'taxes_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Tax Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(null, 'Export is being processed and will be sent to email: ' . $user->email);
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download taxes module import sample template.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import taxes')) {
            return response()->forbidden('Permission denied for downloading taxes import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\SaleAgents\SaleAgentBulkActionRequest;
use App\Http\Requests\SaleAgents\StoreSaleAgentRequest;
use App\Http\Requests\SaleAgents\UpdateSaleAgentRequest;
use App\Http\Resources\SaleAgentResource;
use App\Mail\ExportMail;
use App\Models\Employee;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\SaleAgentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class SaleAgentController
 *
 * API Controller for Sale Agent CRUD, bulk operations, import/export.
 *
 * @group Sale Agent Management
 */
class SaleAgentController extends Controller
{
    public function __construct(
        private readonly SaleAgentService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        if (Auth::user()->denies('view sale agents')) {
            return response()->forbidden('Permission denied for viewing sale agents list.');
        }
        $saleAgents = $this->service->getPaginatedSaleAgents($request->all(), (int) $request->input('per_page', 10));
        return response()->success(SaleAgentResource::collection($saleAgents), 'Sale agents retrieved successfully');
    }

    public function options(): JsonResponse
    {
        if (Auth::user()->denies('view sale agents')) {
            return response()->forbidden('Permission denied for sale agent options.');
        }
        return response()->success($this->service->getOptions(), 'Sale agent options retrieved successfully');
    }

    public function store(StoreSaleAgentRequest $request): JsonResponse
    {
        if (Auth::user()->denies('create sale agents')) {
            return response()->forbidden('Permission denied for create sale agent.');
        }
        $saleAgent = $this->service->createSaleAgent($request->validated());
        return response()->success(
            new SaleAgentResource($saleAgent->load(['department', 'designation', 'shift', 'user'])),
            'Sale agent created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    public function show(Employee $sale_agent): JsonResponse
    {
        if (Auth::user()->denies('view sale agent details')) {
            return response()->forbidden('Permission denied for view sale agent.');
        }
        $saleAgent = $this->service->getSaleAgent($sale_agent);
        return response()->success(new SaleAgentResource($saleAgent), 'Sale agent details retrieved successfully');
    }

    public function update(UpdateSaleAgentRequest $request, Employee $sale_agent): JsonResponse
    {
        if (Auth::user()->denies('update sale agents')) {
            return response()->forbidden('Permission denied for update sale agent.');
        }
        $saleAgent = $this->service->updateSaleAgent($sale_agent, $request->validated());
        return response()->success(new SaleAgentResource($saleAgent), 'Sale agent updated successfully');
    }

    public function destroy(Employee $sale_agent): JsonResponse
    {
        if (Auth::user()->denies('delete sale agents')) {
            return response()->forbidden('Permission denied for delete sale agent.');
        }
        $this->service->deleteSaleAgent($sale_agent);
        return response()->success(null, 'Sale agent deleted successfully');
    }

    public function getAllActive(): JsonResponse
    {
        if (Auth::user()->denies('view sale agents')) {
            return response()->forbidden('Permission denied for viewing active sale agents.');
        }
        return response()->success(
            SaleAgentResource::collection($this->service->getAllActive()),
            'Active sale agents fetched successfully'
        );
    }

    public function bulkDestroy(SaleAgentBulkActionRequest $request): JsonResponse
    {
        if (Auth::user()->denies('delete sale agents')) {
            return response()->forbidden('Permission denied for bulk delete sale agents.');
        }
        $count = $this->service->bulkDeleteSaleAgents($request->validated()['ids']);
        return response()->success(['deleted_count' => $count], "Successfully deleted {$count} sale agents");
    }

    public function bulkActivate(SaleAgentBulkActionRequest $request): JsonResponse
    {
        if (Auth::user()->denies('update sale agents')) {
            return response()->forbidden('Permission denied for bulk update sale agents.');
        }
        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);
        return response()->success(['activated_count' => $count], "{$count} sale agents activated");
    }

    public function bulkDeactivate(SaleAgentBulkActionRequest $request): JsonResponse
    {
        if (Auth::user()->denies('update sale agents')) {
            return response()->forbidden('Permission denied for bulk update sale agents.');
        }
        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);
        return response()->success(['deactivated_count' => $count], "{$count} sale agents deactivated");
    }

    public function import(ImportRequest $request): JsonResponse
    {
        if (Auth::user()->denies('import sale agents')) {
            return response()->forbidden('Permission denied for import sale agents.');
        }
        $this->service->importSaleAgents($request->file('file'));
        return response()->success(null, 'Sale agents imported successfully');
    }

    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (Auth::user()->denies('export sale agents')) {
            return response()->forbidden('Permission denied for export sale agents.');
        }
        $validated = $request->validated();
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            []
        );
        if (($validated['method'] ?? 'download') === 'download') {
            return response()->download(Storage::disk('public')->path($path))->deleteFileAfterSend();
        }
        if ($validated['method'] === 'email') {
            $user = User::query()->find($validated['user_id'] ?? Auth::id());
            if (! $user) {
                return response()->error('User not found for email delivery.');
            }
            $mailSetting = MailSetting::default()->first();
            if (! $mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }
            $generalSetting = GeneralSetting::query()->latest()->first();
            Mail::to($user)->queue(new ExportMail($user, $path, 'sale_agents_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'), 'Your Sale Agents Export Is Ready', $generalSetting, $mailSetting));
            return response()->success(null, 'Export is being processed and will be sent to email: '.$user->email);
        }
        return response()->error('Invalid export method provided.');
    }

    public function download(): JsonResponse|BinaryFileResponse
    {
        if (Auth::user()->denies('import sale agents')) {
            return response()->forbidden('Permission denied for downloading sale agents import template.');
        }
        $path = $this->service->download();
        return response()->download($path, basename($path), ['Content-Type' => 'text/csv']);
    }
}

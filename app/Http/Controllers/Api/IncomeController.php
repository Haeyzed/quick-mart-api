<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Incomes\IncomeBulkActionRequest;
use App\Http\Requests\Incomes\StoreIncomeRequest;
use App\Http\Requests\Incomes\UpdateIncomeRequest;
use App\Http\Resources\IncomeResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\Income;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\IncomeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class IncomeController
 *
 * API Controller for Income CRUD and bulk operations, import/export.
 *
 * @group Income Management
 */
class IncomeController extends Controller
{
    public function __construct(
        private readonly IncomeService $service
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        if (Auth::user()->denies('view incomes')) {
            return response()->forbidden('Permission denied for viewing incomes list.');
        }
        $incomes = $this->service->getPaginatedIncomes($request->all(), (int) $request->input('per_page', 10));
        return response()->success(IncomeResource::collection($incomes), 'Incomes retrieved successfully');
    }

    public function store(StoreIncomeRequest $request): JsonResponse
    {
        if (Auth::user()->denies('create incomes')) {
            return response()->forbidden('Permission denied for create income.');
        }
        $income = $this->service->createIncome($request->validated());
        return response()->success(new IncomeResource($income), 'Income created successfully', ResponseAlias::HTTP_CREATED);
    }

    public function show(Income $income): JsonResponse
    {
        if (Auth::user()->denies('view income details')) {
            return response()->forbidden('Permission denied for view income.');
        }
        $income->load(['warehouse', 'incomeCategory', 'account', 'user', 'cashRegister']);
        return response()->success(new IncomeResource($income), 'Income details retrieved successfully');
    }

    public function update(UpdateIncomeRequest $request, Income $income): JsonResponse
    {
        if (Auth::user()->denies('update incomes')) {
            return response()->forbidden('Permission denied for update income.');
        }
        $updated = $this->service->updateIncome($income, $request->validated());
        return response()->success(new IncomeResource($updated), 'Income updated successfully');
    }

    public function destroy(Income $income): JsonResponse
    {
        if (Auth::user()->denies('delete incomes')) {
            return response()->forbidden('Permission denied for delete income.');
        }
        $this->service->deleteIncome($income);
        return response()->success(null, 'Income deleted successfully');
    }

    public function bulkDestroy(IncomeBulkActionRequest $request): JsonResponse
    {
        if (Auth::user()->denies('delete incomes')) {
            return response()->forbidden('Permission denied for bulk delete incomes.');
        }
        $count = $this->service->bulkDeleteIncomes($request->validated()['ids']);
        return response()->success(['deleted_count' => $count], "Successfully deleted {$count} incomes");
    }

    public function import(ImportRequest $request): JsonResponse
    {
        if (Auth::user()->denies('import incomes')) {
            return response()->forbidden('Permission denied for import incomes.');
        }
        $this->service->importIncomes($request->file('file'));
        return response()->success(null, 'Incomes imported successfully');
    }

    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (Auth::user()->denies('export incomes')) {
            return response()->forbidden('Permission denied for export incomes.');
        }
        $validated = $request->validated();
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            ['start_date' => $validated['start_date'] ?? null, 'end_date' => $validated['end_date'] ?? null]
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
            Mail::to($user)->queue(new ExportMail($user, $path, 'incomes_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'), 'Your Incomes Export Is Ready', $generalSetting, $mailSetting));
            return response()->success(null, 'Export is being processed and will be sent to email: '.$user->email);
        }
        return response()->error('Invalid export method provided.');
    }

    public function download(): JsonResponse|BinaryFileResponse
    {
        if (Auth::user()->denies('import incomes')) {
            return response()->forbidden('Permission denied for downloading incomes import template.');
        }
        $path = $this->service->download();
        return response()->download($path, basename($path), ['Content-Type' => 'text/csv']);
    }
}


<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\States\StateBulkActionRequest;
use App\Http\Requests\States\StoreStateRequest;
use App\Http\Requests\States\UpdateStateRequest;
use App\Http\Resources\StateResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\State;
use App\Models\User;
use App\Services\StateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class StateController
 *
 * API Controller for State listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to StateService.
 *
 * @tags State Management
 */
class StateController extends Controller
{
    /**
     * StateController constructor.
     *
     * @param  StateService  $service  Service handling state business logic.
     */
    public function __construct(
        private readonly StateService $service
    ) {}

    /**
     * List States
     *
     * Display a paginated listing of states. Supports searching and filtering by country.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for viewing states list.');
        }

        $states = $this->service->getPaginatedStates(
            $request->validate([
                /**
                 * Search term to filter states by name or state_code.
                 *
                 * @example "California"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by country ID.
                 *
                 * @example 1
                 */
                'country_id' => ['nullable', 'integer', 'exists:countries,id'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            StateResource::collection($states),
            'States retrieved successfully'
        );
    }

    /**
     * Get state options for select components (value/label format).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for viewing state options.');
        }

        return response()->success($this->service->getOptions(), 'State options retrieved successfully');
    }

    /**
     * Show State
     *
     * Display the specified state.
     */
    public function show(State $state): JsonResponse
    {
        if (auth()->user()->denies('view states')) {
            return response()->forbidden('Permission denied for view state.');
        }

        return response()->success(
            new StateResource($state->load('country')),
            'State details retrieved successfully'
        );
    }

    /**
     * Get city options (value/label) for the specified state.
     *
     * @param  State  $state  State model (route binding).
     */
    public function cities(State $state): JsonResponse
    {
        if (auth()->user()->denies('view cities')) {
            return response()->forbidden('Permission denied for viewing cities by state.');
        }

        $options = $this->service->getCityOptionsByState($state);

        return response()->success($options, 'Cities retrieved successfully');
    }

    /**
     * Create State
     */
    public function store(StoreStateRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create states')) {
            return response()->forbidden('Permission denied for create state.');
        }

        $state = $this->service->createState($request->validated());

        return response()->success(
            new StateResource($state),
            'State created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update State
     */
    public function update(UpdateStateRequest $request, State $state): JsonResponse
    {
        if (auth()->user()->denies('update states')) {
            return response()->forbidden('Permission denied for update state.');
        }

        $updatedState = $this->service->updateState($state, $request->validated());

        return response()->success(
            new StateResource($updatedState),
            'State updated successfully'
        );
    }

    /**
     * Delete State
     */
    public function destroy(State $state): JsonResponse
    {
        if (auth()->user()->denies('delete states')) {
            return response()->forbidden('Permission denied for delete state.');
        }

        $this->service->deleteState($state);

        return response()->success(null, 'State deleted successfully');
    }

    /**
     * Bulk Delete States
     */
    public function bulkDestroy(StateBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete states')) {
            return response()->forbidden('Permission denied for bulk delete states.');
        }

        $count = $this->service->bulkDeleteStates($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} states"
        );
    }

    /**
     * Import States
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import states')) {
            return response()->forbidden('Permission denied for import states.');
        }

        $this->service->importStates($request->file('file'));

        return response()->success(null, 'States imported successfully');
    }

    /**
     * Export States
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export states')) {
            return response()->forbidden('Permission denied for export states.');
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

            if (! $user) {
                return response()->error('User not found for email delivery.');
            }

            $mailSetting = MailSetting::default()->first();

            if (! $mailSetting) {
                return response()->error('System mail settings are not configured. Cannot send email.');
            }

            $generalSetting = GeneralSetting::query()->latest()->first();

            Mail::to($user)->queue(
                new ExportMail(
                    $user,
                    $path,
                    'states_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your State Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: '.$user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download Import Template
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import states')) {
            return response()->forbidden('Permission denied for downloading states import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

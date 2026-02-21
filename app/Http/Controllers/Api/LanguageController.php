<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Languages\LanguageBulkActionRequest;
use App\Http\Requests\Languages\StoreLanguageRequest;
use App\Http\Requests\Languages\UpdateLanguageRequest;
use App\Http\Resources\LanguageResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\Language;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class LanguageController
 *
 * API Controller for Language listing and options (World reference data).
 * Handles authorization via Policy and delegates logic to LanguageService.
 *
 * @tags Language Management
 */
class LanguageController extends Controller
{
    /**
     * LanguageController constructor.
     *
     * @param  LanguageService  $service  Service handling language business logic.
     */
    public function __construct(
        private readonly LanguageService $service
    ) {}

    /**
     * List Languages
     *
     * Display a paginated listing of languages. Supports searching by name or code.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view languages')) {
            return response()->forbidden('Permission denied for viewing languages list.');
        }

        $languages = $this->service->getPaginatedLanguages(
            $request->validate([
                /**
                 * Search term to filter languages by name or code.
                 *
                 * @example "English"
                 */
                'search' => ['nullable', 'string'],
            ]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            LanguageResource::collection($languages),
            'Languages retrieved successfully'
        );
    }

    /**
     * Get language options for select components (value/label format).
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view languages')) {
            return response()->forbidden('Permission denied for viewing language options.');
        }

        return response()->success($this->service->getOptions(), 'Language options retrieved successfully');
    }

    /**
     * Show Language
     *
     * Display the specified language.
     */
    public function show(Language $language): JsonResponse
    {
        if (auth()->user()->denies('view languages')) {
            return response()->forbidden('Permission denied for view language.');
        }

        return response()->success(
            new LanguageResource($language),
            'Language details retrieved successfully'
        );
    }

    /**
     * Create Language
     */
    public function store(StoreLanguageRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create languages')) {
            return response()->forbidden('Permission denied for create language.');
        }

        $language = $this->service->createLanguage($request->validated());

        return response()->success(
            new LanguageResource($language),
            'Language created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Update Language
     */
    public function update(UpdateLanguageRequest $request, Language $language): JsonResponse
    {
        if (auth()->user()->denies('update languages')) {
            return response()->forbidden('Permission denied for update language.');
        }

        $updatedLanguage = $this->service->updateLanguage($language, $request->validated());

        return response()->success(
            new LanguageResource($updatedLanguage),
            'Language updated successfully'
        );
    }

    /**
     * Delete Language
     */
    public function destroy(Language $language): JsonResponse
    {
        if (auth()->user()->denies('delete languages')) {
            return response()->forbidden('Permission denied for delete language.');
        }

        $this->service->deleteLanguage($language);

        return response()->success(null, 'Language deleted successfully');
    }

    /**
     * Bulk Delete Languages
     */
    public function bulkDestroy(LanguageBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete languages')) {
            return response()->forbidden('Permission denied for bulk delete languages.');
        }

        $count = $this->service->bulkDeleteLanguages($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} languages"
        );
    }

    /**
     * Import Languages
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import languages')) {
            return response()->forbidden('Permission denied for import languages.');
        }

        $this->service->importLanguages($request->file('file'));

        return response()->success(null, 'Languages imported successfully');
    }

    /**
     * Export Languages
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export languages')) {
            return response()->forbidden('Permission denied for export languages.');
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
                    'languages_export.'.($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Language Export Is Ready',
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
        if (auth()->user()->denies('import languages')) {
            return response()->forbidden('Permission denied for downloading languages import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

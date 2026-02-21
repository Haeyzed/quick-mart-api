<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LanguageResource;
use App\Models\Language;
use App\Services\LanguageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}

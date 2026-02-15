<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Categories\CategoryBulkActionRequest;
use App\Http\Requests\Categories\ReparentCategoryRequest;
use App\Http\Requests\Categories\StoreCategoryRequest;
use App\Http\Requests\Categories\UpdateCategoryRequest;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Resources\CategoryResource;
use App\Mail\ExportMail;
use App\Models\Category;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\CategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Class CategoryController
 *
 * @group Category Management
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $service
    )
    {
    }

    /**
     * Display a paginated listing of categories.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view categories')) {
            return response()->forbidden('Permission denied for viewing categories list.');
        }

        $categories = $this->service->getPaginatedCategories(
            $request->all(),
            (int)$request->input('per_page', 10)
        );

        return response()->success(
            CategoryResource::collection($categories),
            'Categories retrieved successfully'
        );
    }

    /**
     * Display a tree-view listing of categories.
     */
    public function tree(): JsonResponse
    {
        if (auth()->user()->denies('view categories')) {
            return response()->forbidden('Permission denied for viewing categories tree.');
        }

        $tree = $this->service->getCategoryTree();

        return response()->success(
            CategoryResource::collection($tree),
            'Category tree retrieved successfully'
        );
    }

    /**
     * Store a newly created category.
     */
    public function store(StoreCategoryRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create categories')) {
            return response()->forbidden('Permission denied for create category.');
        }

        $category = $this->service->createCategory($request->validated());

        return response()->success(
            new CategoryResource($category),
            'Category created successfully',
            Response::HTTP_CREATED
        );
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category): JsonResponse
    {
        if (auth()->user()->denies('view category details')) {
            return response()->forbidden('Permission denied for view category.');
        }

        return response()->success(
            new CategoryResource($category),
            'Category details retrieved successfully'
        );
    }

    /**
     * Update the specified category.
     */
    public function update(UpdateCategoryRequest $request, Category $category): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for update category.');
        }

        $updatedCategory = $this->service->updateCategory($category, $request->validated());

        return response()->success(
            new CategoryResource($updatedCategory),
            'Category updated successfully'
        );
    }

    /**
     * Reparent a category (update parent_id via drag-and-drop).
     */
    public function reparent(ReparentCategoryRequest $request, Category $category): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for updating categories.');
        }

        $updatedCategory = $this->service->reparentCategory(
            $category,
            $request->validated()['parent_id']
        );

        return response()->success(
            new CategoryResource($updatedCategory),
            'Category reparented successfully'
        );
    }

    /**
     * Remove the specified category.
     */
    public function destroy(Category $category): JsonResponse
    {
        if (auth()->user()->denies('delete categories')) {
            return response()->forbidden('Permission denied for delete category.');
        }

        $this->service->deleteCategory($category);

        return response()->success(null, 'Category deleted successfully');
    }

    /**
     * Get parent category options.
     */
    public function parents(): JsonResponse
    {
        if (auth()->user()->denies('view categories')) {
            return response()->forbidden('Permission denied for viewing categories list.');
        }

            return response()->success($this->service->getParentOptions(), 'Parent categories retrieved successfully'
        );
    }

    /**
     * Bulk delete categories.
     */
    public function bulkDestroy(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete categories')) {
            return response()->forbidden('Permission denied for bulk delete categories.');
        }

        $count = $this->service->bulkDeleteCategories($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} categories"
        );
    }

    /**
     * Bulk activate categories.
     */
    public function bulkActivate(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], true);

        return response()->success(
            ['activated_count' => $count],
            "{$count} categories activated"
        );
    }

    /**
     * Bulk deactivate categories.
     */
    public function bulkDeactivate(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateStatus($request->validated()['ids'], false);

        return response()->success(
            ['deactivated_count' => $count],
            "{$count} categories deactivated"
        );
    }

    /**
     * Bulk enable featured status.
     */
    public function bulkEnableFeatured(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateFeatured($request->validated()['ids'], true);

        return response()->success(
            ['updated_count' => $count],
            "Enabled featured for {$count} categories"
        );
    }

    /**
     * Bulk disable featured status.
     */
    public function bulkDisableFeatured(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateFeatured($request->validated()['ids'], false);

        return response()->success(
            ['updated_count' => $count],
            "Disabled featured for {$count} categories"
        );
    }

    /**
     * Bulk enable sync.
     */
    public function bulkEnableSync(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateSync($request->validated()['ids'], false);

        return response()->success(
            ['updated_count' => $count],
            "Enabled sync for {$count} categories"
        );
    }

    /**
     * Bulk disable sync.
     */
    public function bulkDisableSync(CategoryBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('update categories')) {
            return response()->forbidden('Permission denied for bulk update categories.');
        }

        $count = $this->service->bulkUpdateSync($request->validated()['ids'], true);

        return response()->success(
            ['updated_count' => $count],
            "Disabled sync for {$count} categories"
        );
    }

    /**
     * Import categories.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import categories')) {
            return response()->forbidden('Permission denied for import categories.');
        }

        $this->service->importCategories($request->file('file'));

        return response()->success(null, 'Categories imported successfully');
    }

    /**
     * Export categories to Excel or PDF.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export categories')) {
            return response()->forbidden('Permission denied for export categories.');
        }

        $validated = $request->validated();

        // 1. Generate the file via service
        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        // 2. Handle Download Method
        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

        // 3. Handle Email Method
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
                    'categories_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your Categories Export Is Ready',
                    $generalSetting,
                    $mailSetting
                )
            );

            return response()->success(
                null,
                'Export is being processed and will be sent to email: ' . $user->email
            );
        }

        return response()->error('Invalid export method provided.');
    }

    /**
     * Download categories import sample template.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import brands')) {
            return response()->forbidden('Permission denied for downloading categories import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

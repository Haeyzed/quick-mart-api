<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PerformanceReviews\PerformanceReviewBulkActionRequest;
use App\Http\Resources\PerformanceReviewResource;
use App\Models\PerformanceReview;
use App\Services\PerformanceReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class PerformanceReviewController
 *
 * API Controller for Performance Review CRUD and bulk operations.
 * Handles authorization via permissions and delegates logic to PerformanceReviewService.
 *
 * @tags HRM Management
 */
class PerformanceReviewController extends Controller
{
    /**
     * PerformanceReviewController constructor.
     */
    public function __construct(
        private readonly PerformanceReviewService $service
    ) {}

    /**
     * List Performance Reviews
     *
     * Display a paginated listing of performance reviews. Supports filtering by employee and status.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /**
                 * Filter by employee ID.
                 *
                 * @example 5
                 */
                'employee_id' => ['nullable', 'integer', 'exists:employees,id'],
                /**
                 * Filter by status (draft, submitted, acknowledged).
                 *
                 * @example "submitted"
                 */
                'status' => ['nullable', 'string'],
            ]),
            /**
             * Amount of items per page.
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(PerformanceReviewResource::collection($items), 'Performance reviews retrieved successfully');
    }

    /**
     * Create Performance Review
     *
     * Store a newly created performance review in the system.
     */
    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            /** @example 5 */
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            /** @example "2024-01-01" */
            'review_period_start' => ['required', 'date'],
            /** @example "2024-12-31" */
            'review_period_end' => ['required', 'date', 'after_or_equal:review_period_start'],
            /** @example 1 */
            'reviewer_id' => ['nullable', 'integer', 'exists:users,id'],
            /** @example 8.5 */
            'overall_rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            /** @example "draft" */
            'status' => ['nullable', 'string', 'in:draft,submitted,acknowledged'],
            /** @example "Strong performance" */
            'notes' => ['nullable', 'string'],
            /** @example "2025-02-01" */
            'promotion_effective_date' => ['nullable', 'date'],
            /** @example 3 */
            'new_designation_id' => ['nullable', 'integer', 'exists:designations,id'],
        ]);

        $model = $this->service->create($data);

        return response()->success(new PerformanceReviewResource($model->load(['employee', 'reviewer', 'newDesignation'])), 'Performance review created successfully', ResponseAlias::HTTP_CREATED);
    }

    /**
     * Show Performance Review
     *
     * Retrieve the details of a specific performance review by its ID.
     */
    public function show(PerformanceReview $performance_review): JsonResponse
    {
        if (auth()->user()->denies('view performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new PerformanceReviewResource($performance_review->load(['employee', 'reviewer', 'newDesignation'])), 'Performance review retrieved successfully');
    }

    /**
     * Update Performance Review
     *
     * Update the specified performance review's information.
     */
    public function update(Request $request, PerformanceReview $performance_review): JsonResponse
    {
        if (auth()->user()->denies('update performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            /** @example "2024-01-01" */
            'review_period_start' => ['sometimes', 'required', 'date'],
            /** @example "2024-12-31" */
            'review_period_end' => ['sometimes', 'required', 'date'],
            /** @example 1 */
            'reviewer_id' => ['nullable', 'integer', 'exists:users,id'],
            /** @example 8.5 */
            'overall_rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            /** @example "submitted" */
            'status' => ['nullable', 'string', 'in:draft,submitted,acknowledged'],
            /** @example "Strong performance" */
            'notes' => ['nullable', 'string'],
            /** @example "2025-02-01" */
            'promotion_effective_date' => ['nullable', 'date'],
            /** @example 3 */
            'new_designation_id' => ['nullable', 'integer', 'exists:designations,id'],
        ]);

        $updated = $this->service->update($performance_review, $data);

        return response()->success(new PerformanceReviewResource($updated), 'Performance review updated successfully');
    }

    /**
     * Delete Performance Review
     *
     * Remove the specified performance review from storage.
     */
    public function destroy(PerformanceReview $performance_review): JsonResponse
    {
        if (auth()->user()->denies('delete performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $this->service->delete($performance_review);

        return response()->success(null, 'Performance review deleted successfully');
    }

    /**
     * Bulk Delete Performance Reviews
     *
     * Delete multiple performance reviews simultaneously using an array of IDs.
     */
    public function bulkDestroy(PerformanceReviewBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete performance reviews')) {
            return response()->forbidden('Permission denied for bulk delete performance reviews.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} performance reviews"
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerformanceReviewResource;
use App\Models\PerformanceReview;
use App\Services\PerformanceReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PerformanceReviewController extends Controller
{
    public function __construct(
        private readonly PerformanceReviewService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate(['employee_id' => ['nullable', 'integer', 'exists:employees,id'], 'status' => ['nullable', 'string']]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(PerformanceReviewResource::collection($items), 'Performance reviews retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            'employee_id' => ['required', 'integer', 'exists:employees,id'],
            'review_period_start' => ['required', 'date'],
            'review_period_end' => ['required', 'date', 'after_or_equal:review_period_start'],
            'reviewer_id' => ['nullable', 'integer', 'exists:users,id'],
            'overall_rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'status' => ['nullable', 'string', 'in:draft,submitted,acknowledged'],
            'notes' => ['nullable', 'string'],
            'promotion_effective_date' => ['nullable', 'date'],
            'new_designation_id' => ['nullable', 'integer', 'exists:designations,id'],
        ]);

        $model = $this->service->create($data);

        return response()->success(new PerformanceReviewResource($model->load(['employee', 'reviewer', 'newDesignation'])), 'Performance review created successfully', ResponseAlias::HTTP_CREATED);
    }

    public function show(PerformanceReview $performance_review): JsonResponse
    {
        if (auth()->user()->denies('view performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new PerformanceReviewResource($performance_review->load(['employee', 'reviewer', 'newDesignation'])), 'Performance review retrieved successfully');
    }

    public function update(Request $request, PerformanceReview $performance_review): JsonResponse
    {
        if (auth()->user()->denies('update performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            'review_period_start' => ['sometimes', 'required', 'date'],
            'review_period_end' => ['sometimes', 'required', 'date'],
            'reviewer_id' => ['nullable', 'integer', 'exists:users,id'],
            'overall_rating' => ['nullable', 'numeric', 'min:0', 'max:10'],
            'status' => ['nullable', 'string', 'in:draft,submitted,acknowledged'],
            'notes' => ['nullable', 'string'],
            'promotion_effective_date' => ['nullable', 'date'],
            'new_designation_id' => ['nullable', 'integer', 'exists:designations,id'],
        ]);

        $updated = $this->service->update($performance_review, $data);

        return response()->success(new PerformanceReviewResource($updated), 'Performance review updated successfully');
    }

    public function destroy(PerformanceReview $performance_review): JsonResponse
    {
        if (auth()->user()->denies('delete performance reviews')) {
            return response()->forbidden('Permission denied.');
        }

        $this->service->delete($performance_review);

        return response()->success(null, 'Performance review deleted successfully');
    }
}

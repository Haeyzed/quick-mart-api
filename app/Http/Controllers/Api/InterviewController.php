<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Interviews\InterviewBulkActionRequest;
use App\Http\Requests\Interviews\StoreInterviewRequest;
use App\Http\Requests\Interviews\UpdateInterviewRequest;
use App\Http\Resources\InterviewResource;
use App\Models\Interview;
use App\Services\InterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class InterviewController
 *
 * API Controller for Interview CRUD operations.
 * Handles authorization via permissions and delegates logic to InterviewService.
 *
 * @tags HRM Management
 */
class InterviewController extends Controller
{
    /**
     * InterviewController constructor.
     */
    public function __construct(
        private readonly InterviewService $service
    ) {}

    /**
     * List Interviews
     *
     * Display a paginated listing of interviews. Supports filtering by candidate and status.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /**
                 * Filter by candidate ID.
                 *
                 * @example 5
                 */
                'candidate_id' => ['nullable', 'integer', 'exists:candidates,id'],
                /**
                 * Filter by interview status (scheduled, completed, cancelled).
                 *
                 * @example "scheduled"
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

        return response()->success(
            InterviewResource::collection($items),
            'Interviews retrieved successfully'
        );
    }

    /**
     * Create Interview
     *
     * Store a newly created interview in the system.
     */
    public function store(StoreInterviewRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $interview = $this->service->create($request->validated());

        return response()->success(
            new InterviewResource($interview->load(['candidate', 'interviewer'])),
            'Interview created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Interview
     *
     * Retrieve the details of a specific interview by its ID.
     */
    public function show(Interview $interview): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(
            new InterviewResource($interview->load(['candidate', 'interviewer'])),
            'Interview retrieved successfully'
        );
    }

    /**
     * Update Interview
     *
     * Update the specified interview's information.
     */
    public function update(UpdateInterviewRequest $request, Interview $interview): JsonResponse
    {
        if (auth()->user()->denies('update candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $updatedInterview = $this->service->update($interview, $request->validated());

        return response()->success(
            new InterviewResource($updatedInterview),
            'Interview updated successfully'
        );
    }

    /**
     * Delete Interview
     *
     * Remove the specified interview from storage.
     */
    public function destroy(Interview $interview): JsonResponse
    {
        if (auth()->user()->denies('delete candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $this->service->delete($interview);

        return response()->success(null, 'Interview deleted successfully');
    }

    /**
     * Bulk Delete Interviews
     *
     * Delete multiple interviews simultaneously using an array of IDs.
     */
    public function bulkDestroy(InterviewBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete candidates')) {
            return response()->forbidden('Permission denied for bulk delete interviews.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} interviews"
        );
    }
}

<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\JobOpenings\JobOpeningBulkActionRequest;
use App\Http\Requests\JobOpenings\StoreJobOpeningRequest;
use App\Http\Requests\JobOpenings\UpdateJobOpeningRequest;
use App\Http\Resources\JobOpeningResource;
use App\Models\JobOpening;
use App\Services\JobOpeningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class JobOpeningController
 *
 * API Controller for Job Opening CRUD operations.
 * Handles authorization via permissions and delegates logic to JobOpeningService.
 *
 * @tags HRM Management
 */
class JobOpeningController extends Controller
{
    /**
     * JobOpeningController constructor.
     */
    public function __construct(
        private readonly JobOpeningService $service
    )
    {
    }

    /**
     * List Job Openings
     *
     * Display a paginated listing of job openings. Supports filtering by status and search.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view job openings')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /**
                 * Filter by status (draft, open, closed).
                 *
                 * @example "open"
                 */
                'status' => ['nullable', 'string'],
                /**
                 * Search term to filter by title.
                 *
                 * @example "Developer"
                 */
                'search' => ['nullable', 'string'],
            ]),
            /**
             * Amount of items per page.
             *
             * @example 50
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(
            JobOpeningResource::collection($items),
            'Job openings retrieved successfully'
        );
    }

    /**
     * Job Opening Options
     *
     * Return a list of job opening options (open status only) for dropdowns.
     */
    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view job openings')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(
            $this->service->getOptions(),
            'Job opening options retrieved successfully'
        );
    }

    /**
     * Create Job Opening
     *
     * Store a newly created job opening in the system.
     */
    public function store(StoreJobOpeningRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create job openings')) {
            return response()->forbidden('Permission denied.');
        }

        $jobOpening = $this->service->create($request->validated());

        return response()->success(
            new JobOpeningResource($jobOpening->load(['department', 'designation'])),
            'Job opening created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show Job Opening
     *
     * Retrieve the details of a specific job opening by its ID.
     */
    public function show(JobOpening $job_opening): JsonResponse
    {
        if (auth()->user()->denies('view job openings')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(
            new JobOpeningResource($job_opening->load(['department', 'designation'])),
            'Job opening retrieved successfully'
        );
    }

    /**
     * Update Job Opening
     *
     * Update the specified job opening's information.
     */
    public function update(UpdateJobOpeningRequest $request, JobOpening $job_opening): JsonResponse
    {
        if (auth()->user()->denies('update job openings')) {
            return response()->forbidden('Permission denied.');
        }

        $updatedJobOpening = $this->service->update($job_opening, $request->validated());

        return response()->success(
            new JobOpeningResource($updatedJobOpening),
            'Job opening updated successfully'
        );
    }

    /**
     * Delete Job Opening
     *
     * Remove the specified job opening from storage.
     */
    public function destroy(JobOpening $job_opening): JsonResponse
    {
        if (auth()->user()->denies('delete job openings')) {
            return response()->forbidden('Permission denied.');
        }

        $this->service->delete($job_opening);

        return response()->success(null, 'Job opening deleted successfully');
    }

    /**
     * Bulk Delete Job Openings
     *
     * Delete multiple job openings simultaneously using an array of IDs.
     */
    public function bulkDestroy(JobOpeningBulkActionRequest $request): JsonResponse
    {
        if (auth()->user()->denies('delete job openings')) {
            return response()->forbidden('Permission denied for bulk delete job openings.');
        }

        $count = $this->service->bulkDelete($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Successfully deleted {$count} job openings"
        );
    }
}

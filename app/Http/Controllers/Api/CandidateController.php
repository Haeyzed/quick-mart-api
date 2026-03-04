<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CandidateResource;
use App\Models\Candidate;
use App\Services\CandidateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class CandidateController
 *
 * API Controller for Candidate CRUD operations.
 * Handles authorization via permissions and delegates logic to CandidateService.
 *
 * @tags HRM Management
 */
class CandidateController extends Controller
{
    /**
     * CandidateController constructor.
     */
    public function __construct(
        private readonly CandidateService $service
    )
    {
    }

    /**
     * List Candidates
     *
     * Display a paginated listing of candidates. Supports filtering by job opening and stage.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate([
                /**
                 * Filter by job opening ID.
                 *
                 * @example 5
                 */
                'job_opening_id' => ['nullable', 'integer', 'exists:job_openings,id'],
                /**
                 * Filter by candidate stage.
                 *
                 * @example "screening"
                 */
                'stage' => ['nullable', 'string'],
                /**
                 * Search term to filter by name or email.
                 *
                 * @example "John"
                 */
                'search' => ['nullable', 'string'],
            ]),
            /**
             * Amount of items per page.
             *
             * @default 10
             */
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(CandidateResource::collection($items), 'Candidates retrieved successfully');
    }

    /**
     * Create Candidate
     *
     * Store a newly created candidate in the system.
     */
    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            /** @example 5 */
            'job_opening_id' => ['required', 'integer', 'exists:job_openings,id'],
            /** @example "Jane Doe" */
            'name' => ['required', 'string', 'max:255'],
            /** @example "jane@example.com" */
            'email' => ['required', 'email'],
            /** @example "+1234567890" */
            'phone' => ['nullable', 'string', 'max:50'],
            /** @example "LinkedIn" */
            'source' => ['nullable', 'string', 'max:100'],
            /** @example "applied" */
            'stage' => ['nullable', 'string', 'in:applied,screening,interview,offer,hired,rejected'],
            /** @example "Strong profile" */
            'notes' => ['nullable', 'string'],
        ]);
        $model = $this->service->create($data);

        return response()->success(new CandidateResource($model->load('jobOpening')), 'Candidate created successfully', ResponseAlias::HTTP_CREATED);
    }

    /**
     * Show Candidate
     *
     * Retrieve the details of a specific candidate by its ID.
     */
    public function show(Candidate $candidate): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new CandidateResource($candidate->load('jobOpening')), 'Candidate retrieved successfully');
    }

    /**
     * Update Candidate
     *
     * Update the specified candidate's information.
     */
    public function update(Request $request, Candidate $candidate): JsonResponse
    {
        if (auth()->user()->denies('update candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            /** @example "Jane Doe" */
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            /** @example "jane@example.com" */
            'email' => ['sometimes', 'required', 'email'],
            /** @example "+1234567890" */
            'phone' => ['nullable', 'string', 'max:50'],
            /** @example "LinkedIn" */
            'source' => ['nullable', 'string', 'max:100'],
            /** @example "interview" */
            'stage' => ['nullable', 'string', 'in:applied,screening,interview,offer,hired,rejected'],
            /** @example "Moved to interview" */
            'notes' => ['nullable', 'string'],
        ]);
        $updated = $this->service->update($candidate, $data);

        return response()->success(new CandidateResource($updated), 'Candidate updated successfully');
    }

    /**
     * Delete Candidate
     *
     * Remove the specified candidate from storage.
     */
    public function destroy(Candidate $candidate): JsonResponse
    {
        if (auth()->user()->denies('delete candidates')) {
            return response()->forbidden('Permission denied.');
        }
        $this->service->delete($candidate);

        return response()->success(null, 'Candidate deleted successfully');
    }
}

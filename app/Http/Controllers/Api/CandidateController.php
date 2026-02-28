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

class CandidateController extends Controller
{
    public function __construct(private readonly CandidateService $service) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }
        $items = $this->service->getPaginated(
            $request->validate(['job_opening_id' => ['nullable', 'integer', 'exists:job_openings,id'], 'stage' => ['nullable', 'string'], 'search' => ['nullable', 'string']]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(CandidateResource::collection($items), 'Candidates retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create candidates')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate([
            'job_opening_id' => ['required', 'integer', 'exists:job_openings,id'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'stage' => ['nullable', 'string', 'in:applied,screening,interview,offer,hired,rejected'],
            'notes' => ['nullable', 'string'],
        ]);
        $model = $this->service->create($data);

        return response()->success(new CandidateResource($model->load('jobOpening')), 'Candidate created successfully', ResponseAlias::HTTP_CREATED);
    }

    public function show(Candidate $candidate): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new CandidateResource($candidate->load('jobOpening')), 'Candidate retrieved successfully');
    }

    public function update(Request $request, Candidate $candidate): JsonResponse
    {
        if (auth()->user()->denies('update candidates')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['sometimes', 'required', 'email'],
            'phone' => ['nullable', 'string', 'max:50'],
            'source' => ['nullable', 'string', 'max:100'],
            'stage' => ['nullable', 'string', 'in:applied,screening,interview,offer,hired,rejected'],
            'notes' => ['nullable', 'string'],
        ]);
        $updated = $this->service->update($candidate, $data);

        return response()->success(new CandidateResource($updated), 'Candidate updated successfully');
    }

    public function destroy(Candidate $candidate): JsonResponse
    {
        if (auth()->user()->denies('delete candidates')) {
            return response()->forbidden('Permission denied.');
        }
        $this->service->delete($candidate);

        return response()->success(null, 'Candidate deleted successfully');
    }
}

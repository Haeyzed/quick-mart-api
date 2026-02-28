<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\JobOpeningResource;
use App\Models\JobOpening;
use App\Services\JobOpeningService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class JobOpeningController extends Controller
{
    public function __construct(private readonly JobOpeningService $service) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view job openings')) {
            return response()->forbidden('Permission denied.');
        }
        $items = $this->service->getPaginated(
            $request->validate(['status' => ['nullable', 'string'], 'search' => ['nullable', 'string']]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(JobOpeningResource::collection($items), 'Job openings retrieved successfully');
    }

    public function options(): JsonResponse
    {
        if (auth()->user()->denies('view job openings')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success($this->service->getOptions(), 'Job opening options retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create job openings')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,id'],
            'status' => ['nullable', 'string', 'in:draft,open,closed'],
            'description' => ['nullable', 'string'],
            'openings_count' => ['nullable', 'integer', 'min:1'],
        ]);
        $model = $this->service->create($data);

        return response()->success(new JobOpeningResource($model->load(['department', 'designation'])), 'Job opening created successfully', ResponseAlias::HTTP_CREATED);
    }

    public function show(JobOpening $job_opening): JsonResponse
    {
        if (auth()->user()->denies('view job openings')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new JobOpeningResource($job_opening->load(['department', 'designation'])), 'Job opening retrieved successfully');
    }

    public function update(Request $request, JobOpening $job_opening): JsonResponse
    {
        if (auth()->user()->denies('update job openings')) {
            return response()->forbidden('Permission denied.');
        }
        $data = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'department_id' => ['nullable', 'integer', 'exists:departments,id'],
            'designation_id' => ['nullable', 'integer', 'exists:designations,id'],
            'status' => ['nullable', 'string', 'in:draft,open,closed'],
            'description' => ['nullable', 'string'],
            'openings_count' => ['nullable', 'integer', 'min:1'],
        ]);
        $updated = $this->service->update($job_opening, $data);

        return response()->success(new JobOpeningResource($updated), 'Job opening updated successfully');
    }

    public function destroy(JobOpening $job_opening): JsonResponse
    {
        if (auth()->user()->denies('delete job openings')) {
            return response()->forbidden('Permission denied.');
        }
        $this->service->delete($job_opening);

        return response()->success(null, 'Job opening deleted successfully');
    }
}

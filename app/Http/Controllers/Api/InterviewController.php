<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\InterviewResource;
use App\Models\Interview;
use App\Services\InterviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class InterviewController extends Controller
{
    public function __construct(
        private readonly InterviewService $service
    ) {}

    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $items = $this->service->getPaginated(
            $request->validate(['candidate_id' => ['nullable', 'integer', 'exists:candidates,id'], 'status' => ['nullable', 'string']]),
            $request->integer('per_page', config('app.per_page'))
        );

        return response()->success(InterviewResource::collection($items), 'Interviews retrieved successfully');
    }

    public function store(Request $request): JsonResponse
    {
        if (auth()->user()->denies('create candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            'candidate_id' => ['required', 'integer', 'exists:candidates,id'],
            'scheduled_at' => ['required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'interviewer_id' => ['nullable', 'integer', 'exists:users,id'],
            'feedback' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:scheduled,completed,cancelled'],
        ]);

        $model = $this->service->create($data);

        return response()->success(new InterviewResource($model->load(['candidate', 'interviewer'])), 'Interview created successfully', ResponseAlias::HTTP_CREATED);
    }

    public function show(Interview $interview): JsonResponse
    {
        if (auth()->user()->denies('view candidates')) {
            return response()->forbidden('Permission denied.');
        }

        return response()->success(new InterviewResource($interview->load(['candidate', 'interviewer'])), 'Interview retrieved successfully');
    }

    public function update(Request $request, Interview $interview): JsonResponse
    {
        if (auth()->user()->denies('update candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $data = $request->validate([
            'scheduled_at' => ['sometimes', 'required', 'date'],
            'duration_minutes' => ['nullable', 'integer', 'min:1'],
            'interviewer_id' => ['nullable', 'integer', 'exists:users,id'],
            'feedback' => ['nullable', 'string'],
            'status' => ['nullable', 'string', 'in:scheduled,completed,cancelled'],
        ]);

        $updated = $this->service->update($interview, $data);

        return response()->success(new InterviewResource($updated), 'Interview updated successfully');
    }

    public function destroy(Interview $interview): JsonResponse
    {
        if (auth()->user()->denies('delete candidates')) {
            return response()->forbidden('Permission denied.');
        }

        $this->service->delete($interview);

        return response()->success(null, 'Interview deleted successfully');
    }
}

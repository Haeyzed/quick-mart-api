<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HolidayBulkDestroyRequest;
use App\Http\Requests\HolidayIndexRequest;
use App\Http\Requests\HolidayRequest;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use App\Services\HolidayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * HolidayController
 *
 * API controller for managing holidays with full CRUD operations.
 */
class HolidayController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @param HolidayService $service
     */
    public function __construct(
        private readonly HolidayService $service
    )
    {
    }

    /**
     * Display a paginated listing of holidays.
     *
     * @param HolidayIndexRequest $request
     * @return JsonResponse
     */
    public function index(HolidayIndexRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $perPage = $validated['per_page'] ?? 10;
        $filters = array_diff_key($validated, array_flip(['per_page', 'page']));

        $holidays = $this->service->getHolidays($filters, $perPage)
            ->through(fn($holiday) => new HolidayResource($holiday));

        return response()->success($holidays, 'Holidays fetched successfully');
    }

    /**
     * Store a newly created holiday.
     *
     * @param HolidayRequest $request
     * @return JsonResponse
     */
    public function store(HolidayRequest $request): JsonResponse
    {
        $holiday = $this->service->createHoliday($request->validated());

        return response()->success(
            new HolidayResource($holiday),
            'Holiday created successfully',
            201
        );
    }

    /**
     * Display the specified holiday.
     *
     * @param Holiday $holiday
     * @return JsonResponse
     */
    public function show(Holiday $holiday): JsonResponse
    {
        return response()->success(
            new HolidayResource($holiday),
            'Holiday retrieved successfully'
        );
    }

    /**
     * Update the specified holiday.
     *
     * @param HolidayRequest $request
     * @param Holiday $holiday
     * @return JsonResponse
     */
    public function update(HolidayRequest $request, Holiday $holiday): JsonResponse
    {
        $holiday = $this->service->updateHoliday($holiday, $request->validated());

        return response()->success(
            new HolidayResource($holiday),
            'Holiday updated successfully'
        );
    }

    /**
     * Remove the specified holiday from storage.
     *
     * @param Holiday $holiday
     * @return JsonResponse
     */
    public function destroy(Holiday $holiday): JsonResponse
    {
        $this->service->deleteHoliday($holiday);

        return response()->success(null, 'Holiday deleted successfully');
    }

    /**
     * Bulk delete multiple holidays.
     *
     * @param HolidayBulkDestroyRequest $request
     * @return JsonResponse
     */
    public function bulkDestroy(HolidayBulkDestroyRequest $request): JsonResponse
    {
        $count = $this->service->bulkDeleteHolidays($request->validated()['ids']);

        return response()->success(
            ['deleted_count' => $count],
            "Deleted {$count} holidays successfully"
        );
    }

    /**
     * Approve a holiday request.
     *
     * @param Holiday $holiday
     * @return JsonResponse
     */
    public function approve(Holiday $holiday): JsonResponse
    {
        $holiday = $this->service->approveHoliday($holiday);

        return response()->success(
            new HolidayResource($holiday),
            'Holiday approved successfully'
        );
    }

    /**
     * Get user holidays for a specific month.
     *
     * @param int $year Year (e.g., 2024)
     * @param int $month Month (1-12)
     * @return JsonResponse
     */
    public function getUserHolidaysByMonth(int $year, int $month): JsonResponse
    {
        $userId = (int)Auth::id();
        $holidays = $this->service->getUserHolidaysByMonth($userId, $year, $month);

        return response()->success(
            $holidays,
            'User holidays retrieved successfully'
        );
    }
}

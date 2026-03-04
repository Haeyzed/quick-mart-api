<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportRequest;
use App\Http\Requests\ImportRequest;
use App\Http\Requests\Users\StoreUserRequest;
use App\Http\Requests\Users\UpdateUserRequest;
use App\Http\Resources\UserResource;
use App\Mail\ExportMail;
use App\Models\GeneralSetting;
use App\Models\MailSetting;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

/**
 * Class UserController
 *
 * API Controller for User CRUD and bulk operations.
 * Handles authorization via Policy and delegates logic to UserService.
 *
 * @tags User Management
 */
class UserController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct(
        private readonly UserService $service
    )
    {
    }

    /**
     * List Users
     *
     * Display a paginated listing of users. Supports searching and filtering by active status and date ranges.
     */
    public function index(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view users')) {
            return response()->forbidden('Permission denied for viewing users list.');
        }

        $users = $this->service->getPaginated(
            $request->validate([
                /**
                 * Search term to filter users by name, email, phone, or username.
                 *
                 * @example "Jane Doe"
                 */
                'search' => ['nullable', 'string'],
                /**
                 * Filter by active status.
                 *
                 * @example true
                 */
                'is_active' => ['nullable', 'boolean'],
                /**
                 * Filter by associated warehouse ID.
                 *
                 * @example 2
                 */
                'warehouse_id' => ['nullable', 'integer', 'exists:warehouses,id'],
                /**
                 * Filter users starting from this date.
                 *
                 * @example "2024-01-01"
                 */
                'start_date' => ['nullable', 'date'],
                /**
                 * Filter users up to this date.
                 *
                 * @example "2024-12-31"
                 */
                'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
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
            UserResource::collection($users),
            'Users retrieved successfully'
        );
    }

    /**
     * Get User Options
     *
     * Retrieve a simplified list of active users for use in dropdowns or select components.
     */
    public function options(Request $request): JsonResponse
    {
        if (auth()->user()->denies('view users')) {
            return response()->forbidden('Permission denied for viewing user options.');
        }

        $warehouseId = $request->filled('warehouse_id') ? (int)$request->input('warehouse_id') : null;

        return response()->success(
            $this->service->getOptions($warehouseId),
            'User options retrieved successfully'
        );
    }

    /**
     * Create User
     *
     * Store a newly created user in the system.
     */
    public function store(StoreUserRequest $request): JsonResponse
    {
        if (auth()->user()->denies('create users')) {
            return response()->forbidden('Permission denied for create user.');
        }

        $user = $this->service->create($request->validated());

        return response()->success(
            new UserResource($user),
            'User created successfully',
            ResponseAlias::HTTP_CREATED
        );
    }

    /**
     * Show User
     *
     * Retrieve the details of a specific user by its ID.
     */
    public function show(User $user): JsonResponse
    {
        if (auth()->user()->denies('view users')) {
            return response()->forbidden('Permission denied for view user.');
        }

        $user = $this->service->getUser($user);

        return response()->success(
            new UserResource($user),
            'User details retrieved successfully'
        );
    }

    /**
     * Update User
     *
     * Update the specified user's information.
     */
    public function update(UpdateUserRequest $request, User $user): JsonResponse
    {
        if (auth()->user()->denies('update users')) {
            return response()->forbidden('Permission denied for update user.');
        }

        $updatedUser = $this->service->update($user, $request->validated());

        return response()->success(
            new UserResource($updatedUser),
            'User updated successfully'
        );
    }

    /**
     * Delete User
     *
     * Remove the specified user from storage.
     */
    public function destroy(User $user): JsonResponse
    {
        if (auth()->user()->denies('delete users')) {
            return response()->forbidden('Permission denied for delete user.');
        }

        $this->service->delete($user);

        return response()->success(null, 'User deleted successfully');
    }

    /**
     * Import Users
     *
     * Import multiple users into the system from an uploaded Excel or CSV file.
     */
    public function import(ImportRequest $request): JsonResponse
    {
        if (auth()->user()->denies('import users')) {
            return response()->forbidden('Permission denied for import users.');
        }

        $this->service->import($request->file('file'));

        return response()->success(null, 'Users imported successfully');
    }

    /**
     * Export Users
     *
     * Export a list of users to an Excel or PDF file. Supports filtering by IDs, date ranges, and selecting specific columns.
     */
    public function export(ExportRequest $request): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('export users')) {
            return response()->forbidden('Permission denied for export users.');
        }

        $validated = $request->validated();

        $path = $this->service->generateExportFile(
            $validated['ids'] ?? [],
            $validated['format'],
            $validated['columns'] ?? [],
            [
                'start_date' => $validated['start_date'] ?? null,
                'end_date' => $validated['end_date'] ?? null,
            ]
        );

        if (($validated['method'] ?? 'download') === 'download') {
            return response()
                ->download(Storage::disk('public')->path($path))
                ->deleteFileAfterSend();
        }

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
                    'users_export.' . ($validated['format'] === 'pdf' ? 'pdf' : 'xlsx'),
                    'Your User Export Is Ready',
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
     * Download Import Template
     *
     * Download a sample CSV template file to assist with formatting data for the bulk import feature.
     */
    public function download(): JsonResponse|BinaryFileResponse
    {
        if (auth()->user()->denies('import users')) {
            return response()->forbidden('Permission denied for downloading users import template.');
        }

        $path = $this->service->download();

        return response()->download(
            $path,
            basename($path),
            ['Content-Type' => 'text/csv']
        );
    }
}

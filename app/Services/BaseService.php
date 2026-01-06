<?php

declare(strict_types=1);

namespace App\Services;

use App\Providers\ResponseServiceProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * BaseService
 *
 * Base service class providing common functionality for all services.
 */
abstract class BaseService
{
    /**
     * Handle database transactions with automatic rollback on exception.
     *
     * @param callable $callback
     * @return mixed
     * @throws Throwable
     */
    protected function transaction(callable $callback): mixed
    {
        return DB::transaction($callback);
    }

    /**
     * Log an error message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logError(string $message, array $context = []): void
    {
        Log::error($message, $context);
    }

    /**
     * Log an info message.
     *
     * @param string $message
     * @param array<string, mixed> $context
     * @return void
     */
    protected function logInfo(string $message, array $context = []): void
    {
        Log::info($message, $context);
    }

    /**
     * Create a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function successResponse(
        mixed  $data = null,
        string $message = 'Success',
        int    $statusCode = 200
    ): JsonResponse
    {
        return ResponseServiceProvider::success($data, $message, $statusCode);
    }

    /**
     * Create an error response.
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     */
    protected function errorResponse(
        string $message = 'An error occurred',
        mixed  $errors = null,
        int    $statusCode = 400
    ): JsonResponse
    {
        return ResponseServiceProvider::error($message, $errors, $statusCode);
    }

    /**
     * Create a validation error response.
     *
     * @param array<string, array<int, string>> $errors
     * @param string $message
     * @return JsonResponse
     */
    protected function validationErrorResponse(
        array  $errors,
        string $message = 'Validation failed'
    ): JsonResponse
    {
        return ResponseServiceProvider::validationError($errors, $message);
    }

    /**
     * Create a not found response.
     *
     * @param string $message
     * @return JsonResponse
     */
    protected function notFoundResponse(string $message = 'Resource not found'): JsonResponse
    {
        return ResponseServiceProvider::notFound($message);
    }
}


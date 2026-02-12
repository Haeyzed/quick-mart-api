<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Response as ResponseFacade;
use Illuminate\Support\ServiceProvider;

/**
 * Class ResponseServiceProvider
 *
 * Registers standardized JSON response helpers for the application.
 * Provides both legacy static methods and modern response macros.
 */
class ResponseServiceProvider extends ServiceProvider
{
    /* -----------------------------------------------------------------
     |  Legacy Static Methods (Backward Compatibility)
     | -----------------------------------------------------------------
     */

    /**
     * Create a successful JSON response.
     *
     * Automatically detects paginated data and appends
     * pagination metadata and navigation links.
     *
     * @param mixed  $data       Response payload
     * @param string $message    Human-readable success message
     * @param int    $statusCode HTTP status code
     *
     * @return JsonResponse
     *
     * @deprecated Use response()->success() macro instead.
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = Response::HTTP_OK
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->getCollection();
            $response['meta'] = [
                'current_page' => $data->currentPage(),
                'per_page' => $data->perPage(),
                'total' => $data->total(),
                'last_page' => $data->lastPage(),
                'from' => $data->firstItem(),
                'to' => $data->lastItem(),
                'has_more' => $data->hasMorePages(),
            ];
            $response['links'] = [
                'first' => $data->url(1),
                'last' => $data->url($data->lastPage()),
                'prev' => $data->previousPageUrl(),
                'next' => $data->nextPageUrl(),
            ];
        } else {
            $response['data'] = $data;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create a validation error JSON response.
     *
     * @param array<string, array<int, string>> $errors
     * @param string $message
     *
     * @return JsonResponse
     *
     * @deprecated Use response()->error() macro instead.
     */
    public static function validationError(
        array $errors,
        string $message = 'Validation failed'
    ): JsonResponse {
        return self::error($message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Create an error JSON response.
     *
     * @param string $message    Error message
     * @param mixed  $errors     Optional error details
     * @param int    $statusCode HTTP status code
     *
     * @return JsonResponse
     *
     * @deprecated Use response()->error() macro instead.
     */
    public static function error(
        string $message = 'An error occurred',
        mixed $errors = null,
        int $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse {
        $response = [
            'success' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create a "not found" JSON response.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, null, Response::HTTP_NOT_FOUND);
    }

    /**
     * Create an unauthorized JSON response.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Create a forbidden JSON response.
     *
     * @param string $message
     *
     * @return JsonResponse
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, null, Response::HTTP_FORBIDDEN);
    }

    /* -----------------------------------------------------------------
     |  Service Provider Lifecycle
     | -----------------------------------------------------------------
     */

    /**
     * Register services.
     *
     * @return void
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap response macros.
     *
     * @return void
     */
    public function boot(): void
    {
        /**
         * Return a standardized success JSON response.
         *
         * Automatically detects paginated responses (including API Resource Collections)
         * and appends pagination metadata and navigation links.
         *
         * @param mixed  $data       Response payload
         * @param string $message    Success message
         * @param int    $statusCode HTTP status code
         *
         * @return JsonResponse
         */
        ResponseFacade::macro('success', function (
            mixed $data = null,
            string $message = 'Success',
            int $statusCode = Response::HTTP_OK
        ): JsonResponse {
            $response = [
                'success' => true,
                'message' => $message,
            ];

            // 1. Handle API Resource Collections wrapping Pagination (e.g. BrandResource::collection($paginator))
            if ($data instanceof AnonymousResourceCollection && $data->resource instanceof LengthAwarePaginator) {
                $paginator = $data->resource;

                // For ResourceCollection, 'collection' contains the transformed items
                $response['data'] = $data->collection;

                $response['meta'] = [
                    'current_page' => $paginator->currentPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'last_page' => $paginator->lastPage(),
                    'from' => $paginator->firstItem(),
                    'to' => $paginator->lastItem(),
                    'has_more' => $paginator->hasMorePages(),
                ];
                $response['links'] = [
                    'first' => $paginator->url(1),
                    'last' => $paginator->url($paginator->lastPage()),
                    'prev' => $paginator->previousPageUrl(),
                    'next' => $paginator->nextPageUrl(),
                ];
            }
            // 2. Handle Raw LengthAwarePaginator passed directly
            elseif ($data instanceof LengthAwarePaginator) {
                $response['data'] = $data->getCollection();
                $response['meta'] = [
                    'current_page' => $data->currentPage(),
                    'per_page' => $data->perPage(),
                    'total' => $data->total(),
                    'last_page' => $data->lastPage(),
                    'from' => $data->firstItem(),
                    'to' => $data->lastItem(),
                    'has_more' => $data->hasMorePages(),
                ];
                $response['links'] = [
                    'first' => $data->url(1),
                    'last' => $data->url($data->lastPage()),
                    'prev' => $data->previousPageUrl(),
                    'next' => $data->nextPageUrl(),
                ];
            }
            // 3. Handle Standard Data
            else {
                $response['data'] = $data;
            }

            return response()->json($response, $statusCode);
        });

        /**
         * Return a standardized error JSON response.
         *
         * @param string $message    Error message
         * @param int    $statusCode HTTP status code
         * @param mixed  $errors     Optional error details
         *
         * @return JsonResponse
         */
        ResponseFacade::macro('error', function (
            string $message = 'Error',
            int $statusCode = Response::HTTP_BAD_REQUEST,
            mixed $errors = null
        ): JsonResponse {
            $response = [
                'success' => false,
                'message' => $message,
            ];

            if ($errors !== null) {
                $response['errors'] = $errors;
            }

            return response()->json($response, $statusCode);
        });
    }
}

<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\ServiceProvider;

/**
 * ResponseServiceProvider
 *
 * Registers response macros for consistent JSON API responses.
 * Macros automatically detect and handle both normal data and paginated results.
 */
class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Create a successful JSON response (legacy static method for backward compatibility).
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return JsonResponse
     * @deprecated Use response()->success() macro instead
     */
    public static function success(
        mixed  $data = null,
        string $message = 'Success',
        int    $statusCode = Response::HTTP_OK
    ): JsonResponse
    {
        $response = [
            'status' => true,
            'message' => $message,
        ];

        if ($data instanceof LengthAwarePaginator) {
            $response['data'] = $data->getCollection();
            $response['pagination'] = [
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
     * Create a validation error JSON response (legacy static method for backward compatibility).
     *
     * @param array<string, array<int, string>> $errors
     * @param string $message
     * @return JsonResponse
     * @deprecated Use response()->error() macro instead
     */
    public static function validationError(
        array  $errors,
        string $message = 'Validation failed'
    ): JsonResponse
    {
        return self::error($message, $errors, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    /**
     * Create an error JSON response (legacy static method for backward compatibility).
     *
     * @param string $message
     * @param mixed $errors
     * @param int $statusCode
     * @return JsonResponse
     * @deprecated Use response()->error() macro instead
     */
    public static function error(
        string $message = 'An error occurred',
        mixed  $errors = null,
        int    $statusCode = Response::HTTP_BAD_REQUEST
    ): JsonResponse
    {
        $response = [
            'status' => false,
            'message' => $message,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }

    /**
     * Create a not found JSON response (legacy static method for backward compatibility).
     *
     * @param string $message
     * @return JsonResponse
     * @deprecated Use response()->error() macro instead
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, null, Response::HTTP_NOT_FOUND);
    }

    /**
     * Create an unauthorized JSON response (legacy static method for backward compatibility).
     *
     * @param string $message
     * @return JsonResponse
     * @deprecated Use response()->error() macro instead
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, null, Response::HTTP_UNAUTHORIZED);
    }

    /**
     * Create a forbidden JSON response (legacy static method for backward compatibility).
     *
     * @param string $message
     * @return JsonResponse
     * @deprecated Use response()->error() macro instead
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, null, Response::HTTP_FORBIDDEN);
    }

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
         * Success response macro.
         *
         * Automatically detects if data is a LengthAwarePaginator and includes
         * pagination metadata and links. Otherwise returns standard response.
         *
         * @param mixed $data The response data (can be array, collection, or paginator)
         * @param string $message Success message
         * @param int $statusCode HTTP status code (default 200)
         * @return JsonResponse
         *
         * @example
         * return response()->success($categories, 'Categories fetched successfully');
         * return response()->success($category, 'Category created successfully', 201);
         */
        \Illuminate\Support\Facades\Response::macro(
            'success',
            function ($data = null, string $message = 'Success', int $statusCode = 200) {
                $response = [
                    'status' => true,
                    'message' => $message,
                ];

                if ($data instanceof LengthAwarePaginator) {
                    $response['data'] = $data->getCollection();
                    $response['pagination'] = [
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
        );

        /**
         * Error response macro.
         *
         * Returns a standardized error response with optional error details.
         *
         * @param string $message Error message
         * @param int $statusCode HTTP status code (default 400)
         * @param mixed $errors Additional error details (optional)
         * @return JsonResponse
         *
         * @example
         * return response()->error('Category not found', 404);
         * return response()->error('Validation failed', 422, $validator->errors());
         */
        \Illuminate\Support\Facades\Response::macro(
            'error',
            function (string $message = 'Error', int $statusCode = 400, $errors = null) {
                $response = [
                    'status' => false,
                    'message' => $message,
                ];

                if ($errors !== null) {
                    $response['errors'] = $errors;
                }

                return response()->json($response, $statusCode);
            }
        );
    }
}


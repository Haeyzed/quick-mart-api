<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;

/**
 * BaseRequest
 *
 * Base form request class that provides consistent validation error handling
 * for all API requests. All form requests should extend this class.
 */
abstract class BaseRequest extends FormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * Uses the ResponseServiceProvider error macro for consistent API responses.
     *
     * @param Validator $validator The validator instance.
     * @throws HttpResponseException
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->error(
                'Validation failed. Please check your input and try again.',
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY,
                $validator->errors()
            )
        );
    }
}




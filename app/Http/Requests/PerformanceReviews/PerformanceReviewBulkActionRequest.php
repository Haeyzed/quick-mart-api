<?php

declare(strict_types=1);

namespace App\Http\Requests\PerformanceReviews;

use App\Http\Requests\BaseRequest;
use Illuminate\Validation\Rule;

/**
 * Class PerformanceReviewBulkActionRequest
 *
 * Handles validation and authorization for bulk actions on performance reviews.
 */
class PerformanceReviewBulkActionRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            /**
             * An array of valid performance review IDs.
             *
             * @example [1, 2, 3]
             */
            'ids' => ['required', 'array', 'min:1'],

            /**
             * A single performance review ID ensuring it exists in the database.
             *
             * @example 1
             */
            'ids.*' => ['required', 'integer', Rule::exists('performance_reviews', 'id')],
        ];
    }
}

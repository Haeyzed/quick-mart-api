<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Biller;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Class BillerResource
 *
 * @mixin Biller
 */
class BillerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            /**
             * The unique identifier for the biller.
             *
             * @example 1
             */
            'id' => $this->id,

            /**
             * The full name of the biller.
             *
             * @example John Doe
             */
            'name' => $this->name,

            /**
             * The email address of the biller.
             *
             * @example johndoe@example.com
             */
            'email' => $this->email,

            /**
             * The primary phone number of the biller (legacy alias).
             *
             * @example +1234567890
             */
            'phone' => $this->phone_number,

            /**
             * The primary phone number of the biller.
             *
             * @example +1234567890
             */
            'phone_number' => $this->phone_number,

            /**
             * The associated company name of the biller.
             *
             * @example ACME Corp
             */
            'company_name' => $this->company_name,

            /**
             * The VAT or Tax Identification Number.
             *
             * @example VAT-987654321
             */
            'vat_number' => $this->vat_number,

            /**
             * The street address of the biller.
             *
             * @example 123 Main Street, Suite 400
             */
            'address' => $this->address,

            /**
             * The ID of the associated country.
             *
             * @example 1
             */
            'country_id' => $this->country_id,

            /**
             * The ID of the associated state.
             *
             * @example 12
             */
            'state_id' => $this->state_id,

            /**
             * The ID of the associated city.
             *
             * @example 45
             */
            'city_id' => $this->city_id,

            /**
             * The loaded country relationship data.
             *
             * @example {"id": 1, "name": "United States"}
             */
            'country' => $this->whenLoaded('country', fn () => $this->country ? ['id' => $this->country->id, 'name' => $this->country->name] : null),

            /**
             * The loaded state relationship data.
             *
             * @example {"id": 12, "name": "California"}
             */
            'state' => $this->whenLoaded('state', fn () => $this->state ? ['id' => $this->state->id, 'name' => $this->state->name] : null),

            /**
             * The loaded city relationship data.
             *
             * @example {"id": 45, "name": "Los Angeles"}
             */
            'city' => $this->whenLoaded('city', fn () => $this->city ? ['id' => $this->city->id, 'name' => $this->city->name] : null),

            /**
             * The postal or zip code.
             *
             * @example 90001
             */
            'postal_code' => $this->postal_code,

            /**
             * The relative path to the biller's image.
             *
             * @example images/billers/avatar.png
             */
            'image' => $this->image,

            /**
             * The absolute URL to the biller's image.
             *
             * @example https://api.example.com/storage/images/billers/avatar.png
             */
            'image_url' => $this->image_url,

            /**
             * Indicates if the biller is currently active.
             *
             * @example true
             */
            'is_active' => $this->is_active,

            /**
             * The human-readable active status.
             *
             * @example active
             */
            'active_status' => $this->is_active ? 'active' : 'inactive',

            /**
             * The date and time when the record was created.
             *
             * @example 2024-01-01T12:00:00Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * The date and time when the record was last updated.
             *
             * @example 2024-01-02T12:00:00Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}

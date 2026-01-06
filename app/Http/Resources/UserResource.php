<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * UserResource
 *
 * Transforms a User model instance into a JSON response with full documentation
 * for each field to ensure API documentation clarity.
 */
class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            /**
             * The unique identifier for the user.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * The user's name.
             *
             * @var string $name
             * @example John Doe
             */
            'name' => $this->name,

            /**
             * The user's email address.
             *
             * @var string|null $email
             * @example john.doe@example.com
             */
            'email' => $this->email,

            /**
             * The user's phone number.
             *
             * @var string|null $phone
             * @example +1234567890
             */
            'phone' => $this->phone,

            /**
             * The user's company name.
             *
             * @var string|null $company_name
             * @example Acme Corporation
             */
            'company_name' => $this->company_name,

            /**
             * The user's role ID.
             *
             * @var int|null $role_id
             * @example 1
             */
            'role_id' => $this->role_id,

            /**
             * The user's biller ID.
             *
             * @var int|null $biller_id
             * @example 1
             */
            'biller_id' => $this->biller_id,

            /**
             * The user's warehouse ID.
             *
             * @var int|null $warehouse_id
             * @example 1
             */
            'warehouse_id' => $this->warehouse_id,

            /**
             * Whether the user is active.
             *
             * @var bool $is_active
             * @example true
             */
            'is_active' => (bool)$this->is_active,

            /**
             * Whether the user is deleted.
             *
             * @var bool $is_deleted
             * @example false
             */
            'is_deleted' => (bool)$this->is_deleted,

            /**
             * ISO 8601 formatted email verification timestamp.
             *
             * @var string|null $email_verified_at
             * @example 2024-01-15T10:30:00.000000Z
             */
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),

            /**
             * ISO 8601 formatted creation timestamp.
             *
             * @var string|null $created_at
             * @example 2024-01-15T10:30:00.000000Z
             */
            'created_at' => $this->created_at?->toIso8601String(),

            /**
             * ISO 8601 formatted last update timestamp.
             *
             * @var string|null $updated_at
             * @example 2024-01-15T15:45:00.000000Z
             */
            'updated_at' => $this->updated_at?->toIso8601String(),

            /**
             * Biller relationship (if loaded).
             *
             * @var array<string, mixed>|null $biller
             */
            'biller' => $this->whenLoaded('biller'),

            /**
             * Warehouse relationship (if loaded).
             *
             * @var array<string, mixed>|null $warehouse
             */
            'warehouse' => $this->whenLoaded('warehouse'),

            /**
             * Roles relationship (if loaded).
             *
             * @var array<string, mixed>|null $roles
             */
            'roles' => $this->whenLoaded('roles'),
        ];
    }
}


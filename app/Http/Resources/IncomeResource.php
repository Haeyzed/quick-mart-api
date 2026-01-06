<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * IncomeResource
 *
 * API resource for transforming Income model data.
 */
class IncomeResource extends JsonResource
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
             * Income ID.
             *
             * @var int $id
             * @example 1
             */
            'id' => $this->id,

            /**
             * Reference number for the income.
             *
             * @var string $reference_no
             * @example ir-20240101-120000
             */
            'reference_no' => $this->reference_no,

            /**
             * Income category information.
             *
             * @var array<string, mixed>|null $income_category
             */
            'income_category' => $this->whenLoaded('incomeCategory', function () {
                return [
                    'id' => $this->incomeCategory->id,
                    'code' => $this->incomeCategory->code,
                    'name' => $this->incomeCategory->name,
                ];
            }),

            /**
             * Warehouse information.
             *
             * @var array<string, mixed>|null $warehouse
             */
            'warehouse' => $this->whenLoaded('warehouse', function () {
                return [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                ];
            }),

            /**
             * Account information.
             *
             * @var array<string, mixed>|null $account
             */
            'account' => $this->whenLoaded('account', function () {
                return [
                    'id' => $this->account->id,
                    'name' => $this->account->name,
                    'account_no' => $this->account->account_no,
                ];
            }),

            /**
             * User information.
             *
             * @var array<string, mixed>|null $user
             */
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),

            /**
             * Cash register ID.
             *
             * @var int|null $cash_register_id
             * @example 1
             */
            'cash_register_id' => $this->cash_register_id,

            /**
             * Income amount.
             *
             * @var float $amount
             * @example 1000.00
             */
            'amount' => $this->amount,

            /**
             * Optional note about the income.
             *
             * @var string|null $note
             * @example Payment received for services
             */
            'note' => $this->note,

            /**
             * Timestamp when the income was created.
             *
             * @var string|null $created_at
             * @example 2024-01-01T12:00:00.000000Z
             */
            'created_at' => $this->created_at?->toISOString(),

            /**
             * Timestamp when the income was last updated.
             *
             * @var string|null $updated_at
             * @example 2024-01-01T12:00:00.000000Z
             */
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}


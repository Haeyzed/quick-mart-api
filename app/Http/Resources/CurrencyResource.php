<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Currency
 */
class CurrencyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'precision' => $this->precision,
            'symbol' => $this->symbol,
            'symbol_native' => $this->symbol_native,
            'symbol_first' => $this->symbol_first,
            'decimal_mark' => $this->decimal_mark,
            'thousands_separator' => $this->thousands_separator,
            'country_id' => $this->country_id,
            'country' => $this->whenLoaded('country', fn () => $this->country ? ['id' => $this->country->id, 'name' => $this->country->name] : null),
        ];
    }
}

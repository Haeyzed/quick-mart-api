<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PosSettingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $options = !empty($this->payment_options)
            ? explode(',', $this->payment_options)
            : [];

        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'warehouse_id' => $this->warehouse_id,
            'biller_id' => $this->biller_id,
            'product_number' => $this->product_number,
            'keybord_active' => $this->keybord_active,
            'is_table' => $this->is_table,
            'send_sms' => (bool)($this->send_sms ?? false),
            'cash_register' => (bool)($this->cash_register ?? false),
            'stripe_public_key' => $this->stripe_public_key,
            'stripe_secret_key' => $this->stripe_secret_key ? '********' : null,
            'paypal_live_api_username' => $this->paypal_live_api_username,
            'paypal_live_api_password' => $this->paypal_live_api_password ? '********' : null,
            'paypal_live_api_secret' => $this->paypal_live_api_secret ? '********' : null,
            'payment_options' => $options,
            'show_print_invoice' => $this->show_print_invoice,
            'invoice_option' => $this->invoice_option,
            'thermal_invoice_size' => $this->thermal_invoice_size,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}

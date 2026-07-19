<?php

namespace App\Http\Resources\Payment;

use App\Models\Payment\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin PaymentMethod */
class PaymentMethodResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'code'            => $this->code,
            'display_name'    => $this->display_name,
            'logo_url'        => $this->logo_url,
            'instruction'     => $this->instruction_template,
            'fee_flat'        => $this->fee_flat,
            'fee_percent_bp'  => $this->fee_percent_bp,
            'fee_borne_by'    => $this->fee_borne_by,
            'borne_by_parent' => $this->feeBorneByParent(),
            'min_amount'      => $this->min_amount,
            'max_amount'      => $this->max_amount,
            'expiry_minutes'  => $this->expiry_minutes,
            'api_format'      => $this->whenLoaded('provider', fn () => $this->provider->api_format),
        ];
    }
}

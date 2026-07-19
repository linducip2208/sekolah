<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchoolResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'name'      => $this->name,
            'subdomain' => $this->subdomain,
            'logo_url'  => $this->logo_url,
            'timezone'  => $this->timezone,
            'locale'    => $this->locale,
        ];
    }
}

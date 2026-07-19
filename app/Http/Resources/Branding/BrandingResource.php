<?php

namespace App\Http\Resources\Branding;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BrandingResource extends JsonResource
{
    /**
     * Pass an array (output of BrandingService::toArray()).
     */
    public function toArray(Request $request): array
    {
        return [
            'display_name'         => $this->resource['display_name'] ?? null,
            'tagline'              => $this->resource['tagline'] ?? null,
            'school_type_label'    => $this->resource['school_type_label'] ?? null,
            'academic_year_format' => $this->resource['academic_year_format'] ?? null,
            'colors'               => $this->resource['colors'] ?? [],
            'background_mode'      => $this->resource['background_mode'] ?? 'light',
            'logos'                => $this->resource['logos'] ?? [],
            'login'                => $this->resource['login'] ?? [],
            'mobile'               => $this->resource['mobile'] ?? [],
            'pdf'                  => $this->resource['pdf'] ?? [],
            'cache_version'        => $this->resource['cache_version'] ?? 1,
        ];
    }
}

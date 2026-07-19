<?php

namespace App\Http\Requests\AI;

use App\Models\AI\AiProvider;
use Illuminate\Foundation\Http\FormRequest;

class StoreAiProviderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('admin') ?? false;
    }

    public function rules(): array
    {
        return [
            'name'          => 'required|string|max:200',
            'api_format'    => 'required|in:' . implode(',', AiProvider::FORMATS),
            'base_url'      => 'required|url|max:500',
            'api_key'       => 'nullable|string|max:500',
            'extra_headers' => 'nullable|array',
            'extra_config'  => 'nullable|array',
            'is_active'     => 'nullable|boolean',
            'priority'      => 'nullable|integer|min:0|max:1000',
        ];
    }
}
